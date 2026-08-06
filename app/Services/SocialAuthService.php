<?php

namespace App\Services;

use App\Core\App;

/** deploy-marker: hopn-sportify-google-oauth-20260720f */
class SocialAuthService
{
    private const STATE_TTL = 900;

    private const GOOGLE_AUTH = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN = 'https://oauth2.googleapis.com/token';
    /** Same userinfo surface as AI Pass php-auth GoogleOAuth (Oauth2 userinfo). */
    private const GOOGLE_USERINFO = 'https://www.googleapis.com/oauth2/v3/userinfo';

    private const FACEBOOK_AUTH = 'https://www.facebook.com/v18.0/dialog/oauth';
    private const FACEBOOK_TOKEN = 'https://graph.facebook.com/v18.0/oauth/access_token';
    private const FACEBOOK_GRAPH = 'https://graph.facebook.com/v18.0/me';

    private const LINKEDIN_AUTH = 'https://www.linkedin.com/oauth/v2/authorization';
    private const LINKEDIN_TOKEN = 'https://www.linkedin.com/oauth/v2/accessToken';
    private const LINKEDIN_USERINFO = 'https://api.linkedin.com/v2/userinfo';

    public static function isConfigured(string $provider): bool
    {
        return match ($provider) {
            'google'   => self::googleClientId() !== '' && self::googleClientSecret() !== '',
            'facebook' => self::facebookAppId() !== '' && self::facebookAppSecret() !== '',
            'linkedin' => self::linkedinClientId() !== '' && self::linkedinClientSecret() !== '',
            default    => false,
        };
    }

    /** @return list<string> */
    public static function configuredProviders(): array
    {
        $out = [];
        foreach (['google', 'facebook', 'linkedin'] as $provider) {
            if (self::isConfigured($provider)) {
                $out[] = $provider;
            }
        }
        return $out;
    }

    public static function googleAuthUrl(string $from = 'login', string $role = 'player'): string
    {
        self::requireConfigured('google');

        $state = self::issueState('google', self::stateMeta($from, $role));
        $params = [
            'client_id'     => self::googleClientId(),
            'redirect_uri'  => self::redirectUri('google'),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ];

        return self::GOOGLE_AUTH . '?' . http_build_query($params);
    }

    /** @return array{name:?string,email:string,provider:string,provider_id:string,avatar_url:?string} */
    public static function handleGoogleCallback(string $code, ?string $state): array
    {
        self::requireConfigured('google');
        self::applyStateMeta('google', $state);

        $token = self::httpPost(self::GOOGLE_TOKEN, [
            'code'          => $code,
            'client_id'     => self::googleClientId(),
            'client_secret' => self::googleClientSecret(),
            'redirect_uri'  => self::redirectUri('google'),
            'grant_type'    => 'authorization_code',
        ]);

        if (isset($token['error'])) {
            throw new \RuntimeException(
                'Google token error: ' . ($token['error_description'] ?? $token['error'])
            );
        }

        if (empty($token['access_token'])) {
            $detail = trim((string) ($token['error_description'] ?? $token['error'] ?? ''));
            $msg = 'Google token exchange failed.';
            if ($detail !== '') {
                $msg .= ' ' . $detail;
            } else {
                $msg .= ' Check redirect URI and client credentials in Google Cloud Console.';
            }
            throw new \RuntimeException($msg);
        }

        $profile = self::httpGet(
            self::GOOGLE_USERINFO,
            ['Authorization: Bearer ' . $token['access_token']]
        );

        $email = strtolower(trim((string) ($profile['email'] ?? '')));
        $googleId = (string) ($profile['sub'] ?? $profile['id'] ?? '');

        if ($email === '' || $googleId === '') {
            throw new \RuntimeException('Google did not return required profile fields.');
        }

        return [
            'google_id'   => $googleId,
            'name'        => trim((string) ($profile['name'] ?? $profile['given_name'] ?? '')) ?: null,
            'email'       => $email,
            'provider'    => 'google',
            'provider_id' => $googleId,
            'avatar_url'  => isset($profile['picture']) ? (string) $profile['picture'] : null,
        ];
    }

    /** Facebook Login scopes — email requires Meta Use Case permission; public_profile works without app review. */
    public static function facebookScopes(): string
    {
        $configured = trim((string) App::config('social.facebook.scopes', ''));
        if ($configured !== '') {
            return $configured;
        }

        return 'public_profile';
    }

    public static function facebookAuthUrl(string $from = 'login', string $role = 'player'): string
    {
        self::requireConfigured('facebook');

        $state = self::issueState('facebook', self::stateMeta($from, $role));
        $params = [
            'client_id'     => self::facebookAppId(),
            'redirect_uri'  => self::redirectUri('facebook'),
            'state'         => $state,
            'scope'         => self::facebookScopes(),
            'response_type' => 'code',
        ];

        return self::FACEBOOK_AUTH . '?' . http_build_query($params);
    }

    /** @return array{name:string,email:string,provider:string,provider_id:string} */
    public static function handleFacebookCallback(string $code, ?string $state): array
    {
        self::requireConfigured('facebook');
        self::applyStateMeta('facebook', $state);

        $token = self::httpGet(self::FACEBOOK_TOKEN . '?' . http_build_query([
            'client_id'     => self::facebookAppId(),
            'client_secret' => self::facebookAppSecret(),
            'redirect_uri'  => self::redirectUri('facebook'),
            'code'          => $code,
        ]));

        if (empty($token['access_token'])) {
            $detail = trim((string) ($token['error_description'] ?? $token['error']['message'] ?? $token['error'] ?? ''));
            if (is_array($token['error'])) {
                $detail = trim((string) ($token['error']['message'] ?? json_encode($token['error'])));
            }
            $msg = 'Facebook token exchange failed.';
            if ($detail !== '') {
                $msg .= ' ' . $detail;
            } else {
                $msg .= ' Check app credentials and redirect URI in Meta for Developers.';
            }
            throw new \RuntimeException($msg);
        }

        $providerId = '';
        $profile = self::httpGet(self::FACEBOOK_GRAPH . '?' . http_build_query([
            'fields'       => 'id,name,email',
            'access_token' => $token['access_token'],
        ]));

        $providerId = (string) ($profile['id'] ?? '');
        if ($providerId === '') {
            throw new \RuntimeException('Facebook did not return a user id.');
        }

        $email = strtolower(trim((string) ($profile['email'] ?? '')));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = self::facebookFallbackEmail($providerId);
        }

        return [
            'name'        => trim((string) ($profile['name'] ?? 'Sportify User')),
            'email'       => $email,
            'provider'    => 'facebook',
            'provider_id' => $providerId,
        ];
    }

    protected static function facebookFallbackEmail(string $providerId): string
    {
        $safeId = preg_replace('/[^0-9a-zA-Z]/', '', $providerId) ?: bin2hex(random_bytes(8));

        return 'fb' . $safeId . '@oauth.sportify.local';
    }

    public static function linkedinAuthUrl(string $from = 'login', string $role = 'player'): string
    {
        self::requireConfigured('linkedin');

        $state = self::issueState('linkedin', self::stateMeta($from, $role));
        $params = [
            'response_type' => 'code',
            'client_id'     => self::linkedinClientId(),
            'redirect_uri'  => self::redirectUri('linkedin'),
            'state'         => $state,
            'scope'         => 'openid profile email',
        ];

        return self::LINKEDIN_AUTH . '?' . http_build_query($params);
    }

    /** @return array{name:string,email:string,provider:string,provider_id:string} */
    public static function handleLinkedInCallback(string $code, ?string $state): array
    {
        self::requireConfigured('linkedin');
        self::applyStateMeta('linkedin', $state);

        $token = self::httpPost(self::LINKEDIN_TOKEN, [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => self::redirectUri('linkedin'),
            'client_id'     => self::linkedinClientId(),
            'client_secret' => self::linkedinClientSecret(),
        ]);

        if (empty($token['access_token'])) {
            $detail = trim((string) ($token['error_description'] ?? $token['error'] ?? ''));
            $msg = 'LinkedIn token exchange failed.';
            if ($detail !== '') {
                $msg .= ' ' . $detail;
            } else {
                $msg .= ' Check redirect URI and client credentials in the LinkedIn Developer Portal.';
            }
            throw new \RuntimeException($msg);
        }

        $profile = self::httpGet(
            self::LINKEDIN_USERINFO,
            ['Authorization: Bearer ' . $token['access_token']]
        );

        if (empty($profile['email'])) {
            throw new \RuntimeException('LinkedIn did not return an email address for this account.');
        }

        $name = trim((string) ($profile['name'] ?? ''));
        if ($name === '') {
            $name = trim(trim((string) ($profile['given_name'] ?? '')) . ' ' . trim((string) ($profile['family_name'] ?? '')));
        }
        if ($name === '') {
            $name = 'Sportify User';
        }

        return [
            'name'        => $name,
            'email'       => strtolower((string) $profile['email']),
            'provider'    => 'linkedin',
            'provider_id' => (string) ($profile['sub'] ?? ''),
        ];
    }

    public static function redirectUri(string $provider): string
    {
        if ($provider === 'google') {
            // Socialite order: GOOGLE_REDIRECT_URI → services.google.redirect → APP_URL/callback
            foreach ([
                App::config('social.google.redirect_uri', ''),
                App::config('services.google.redirect', ''),
            ] as $candidate) {
                $configured = trim((string) $candidate);
                if ($configured !== '') {
                    return rtrim($configured, '/');
                }
            }
        }

        $base = rtrim((string) App::config('app.url', ''), '/');
        if ($base === '') {
            $base = 'https://sportifyplus.de';
        }

        return $base . '/auth/' . $provider . '/callback';
    }

    public static function setupHint(string $provider): string
    {
        return match ($provider) {
            'google'   => 'Add GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and GOOGLE_REDIRECT_URI=' . self::redirectUri('google') . ' to .env in hPanel File Manager, then register that redirect URI in Google Cloud Console (dedicated Sportify OAuth client in the HOPn project).',
            'facebook' => 'Add FACEBOOK_APP_ID and FACEBOOK_APP_SECRET to .env in hPanel File Manager, then add ' . self::redirectUri('facebook') . ' as a valid OAuth redirect in Meta for Developers. Enable Facebook Login under Use Cases and add the email permission before requesting FACEBOOK_SCOPES=public_profile,email.',
            'linkedin' => 'Add LINKEDIN_CLIENT_ID and LINKEDIN_CLIENT_SECRET to .env in hPanel File Manager, then register ' . self::redirectUri('linkedin') . ' as a redirect URL in the LinkedIn Developer Portal.',
            default    => 'Configure OAuth credentials in .env.',
        };
    }

    /** @return array{from?:string,role?:string} */
    protected static function stateMeta(string $from, string $role): array
    {
        $meta = ['from' => $from];
        if ($from === 'register') {
            $meta['role'] = $role;
        }
        return $meta;
    }

    /** @param array{from?:string,role?:string} $meta */
    protected static function issueState(string $provider, array $meta = []): string
    {
        $nonce = bin2hex(random_bytes(16));
        $_SESSION['oauth_state_' . $provider] = $nonce;

        $payload = array_merge([
            'n'   => $nonce,
            'exp' => time() + self::STATE_TTL,
            'p'   => $provider,
        ], $meta);
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Could not build OAuth state.');
        }

        $body = self::base64UrlEncode($json);
        $sig = self::base64UrlEncode(hash_hmac('sha256', $body, self::stateSigningKey(), true));

        return $body . '.' . $sig;
    }

    protected static function applyStateMeta(string $provider, ?string $state): void
    {
        if ($state === null || $state === '') {
            throw new \RuntimeException('Invalid OAuth state. Please try signing in again.');
        }

        $decoded = self::verifyState($provider, $state);

        $from = (string) ($decoded['from'] ?? 'login');
        if ($from === 'register') {
            $_SESSION['oauth_registering'] = true;
            $_SESSION['oauth_pending_role'] = (string) ($decoded['role'] ?? 'player');
        } else {
            unset($_SESSION['oauth_registering'], $_SESSION['oauth_pending_role']);
        }
    }

    /** @return array<string,mixed> */
    protected static function verifyState(string $provider, string $state): array
    {
        if (str_contains($state, '.')) {
            [$body, $sig] = explode('.', $state, 2);
            $expected = self::base64UrlEncode(hash_hmac('sha256', $body, self::stateSigningKey(), true));
            if (!hash_equals($expected, $sig)) {
                throw new \RuntimeException('Invalid OAuth state. Please try signing in again.');
            }

            $decoded = json_decode(self::base64UrlDecode($body), true);
            if (!is_array($decoded) || ($decoded['p'] ?? '') !== $provider) {
                throw new \RuntimeException('Invalid OAuth state. Please try signing in again.');
            }
            if ((int) ($decoded['exp'] ?? 0) < time()) {
                throw new \RuntimeException('OAuth session expired. Please try signing in again.');
            }

            unset($_SESSION['oauth_state_' . $provider]);

            return $decoded;
        }

        $expected = (string) ($_SESSION['oauth_state_' . $provider] ?? '');
        unset($_SESSION['oauth_state_' . $provider]);
        if ($expected === '') {
            throw new \RuntimeException('Invalid OAuth state. Please try signing in again.');
        }

        $decoded = json_decode(self::base64UrlDecode($state), true);
        if (!is_array($decoded) || !hash_equals($expected, (string) ($decoded['n'] ?? ''))) {
            throw new \RuntimeException('Invalid OAuth state. Please try signing in again.');
        }

        return $decoded;
    }

    protected static function stateSigningKey(): string
    {
        return (string) App::config('app.key', 'sportify-dev-key-change-me');
    }

    protected static function requireConfigured(string $provider): void
    {
        if (!self::isConfigured($provider)) {
            throw new \RuntimeException(self::setupHint($provider));
        }
    }

    protected static function googleClientId(): string
    {
        $id = (string) App::config('social.google.client_id', '');
        if ($id === '') {
            $id = (string) App::config('services.google.client_id', '');
        }
        return $id;
    }

    protected static function googleClientSecret(): string
    {
        $secret = (string) App::config('social.google.client_secret', '');
        if ($secret === '') {
            $secret = (string) App::config('services.google.client_secret', '');
        }
        return $secret;
    }

    protected static function facebookAppId(): string
    {
        return (string) App::config('social.facebook.app_id', '');
    }

    protected static function facebookAppSecret(): string
    {
        return (string) App::config('social.facebook.app_secret', '');
    }

    protected static function linkedinClientId(): string
    {
        return (string) App::config('social.linkedin.client_id', '');
    }

    protected static function linkedinClientSecret(): string
    {
        return (string) App::config('social.linkedin.client_secret', '');
    }

    protected static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return (string) base64_decode(strtr($data, '-_', '+/'), true);
    }

    /** @return array<string,mixed> */
    protected static function httpPost(string $url, array $params, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_HTTPHEADER     => array_merge(['Content-Type: application/x-www-form-urlencoded'], $headers),
            CURLOPT_TIMEOUT        => 20,
        ]);
        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno) {
            throw new \RuntimeException('OAuth request failed (network error).');
        }

        return json_decode((string) $raw, true) ?: [];
    }

    /** @return array<string,mixed> */
    protected static function httpGet(string $url, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 20,
        ]);
        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno) {
            throw new \RuntimeException('OAuth request failed (network error).');
        }

        return json_decode((string) $raw, true) ?: [];
    }
}
