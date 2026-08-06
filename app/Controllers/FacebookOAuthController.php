<?php

namespace App\Controllers;

use App\Services\SocialAuthService;

/** deploy-marker: fb-data-deletion-20260621 */
class FacebookOAuthController extends OAuthController
{
    private const FACEBOOK_TOKEN = 'https://graph.facebook.com/v18.0/oauth/access_token';
    private const FACEBOOK_GRAPH = 'https://graph.facebook.com/v18.0/me';

    public function facebook(): string
    {
        return $this->start('facebook', static fn (string $from, string $role) => SocialAuthService::facebookAuthUrl($from, $role));
    }

    public function facebookCallback(): string
    {
        return $this->finish('facebook', function (string $code, ?string $state): array {
            try {
                return SocialAuthService::handleFacebookCallback($code, $state);
            } catch (\RuntimeException $e) {
                if (!str_contains(strtolower($e->getMessage()), 'email')) {
                    throw $e;
                }
            }

            return $this->fetchFacebookProfile($code, $state);
        });
    }

    /** @return array{name:string,email:string,provider:string,provider_id:string} */
    private function fetchFacebookProfile(string $code, ?string $state): array
    {
        $ref = new \ReflectionClass(SocialAuthService::class);

        $ref->getMethod('applyStateMeta')->setAccessible(true);
        $ref->getMethod('applyStateMeta')->invoke(null, 'facebook', $state);

        $httpGet = $ref->getMethod('httpGet');
        $httpGet->setAccessible(true);
        $appId = $ref->getMethod('facebookAppId');
        $appId->setAccessible(true);
        $appSecret = $ref->getMethod('facebookAppSecret');
        $appSecret->setAccessible(true);

        $token = $httpGet->invoke(null, self::FACEBOOK_TOKEN . '?' . http_build_query([
            'client_id'     => $appId->invoke(null),
            'client_secret' => $appSecret->invoke(null),
            'redirect_uri'  => SocialAuthService::redirectUri('facebook'),
            'code'          => $code,
        ]));

        if (empty($token['access_token'])) {
            throw new \RuntimeException('Facebook token exchange failed. Check app credentials and redirect URI.');
        }

        $profile = $httpGet->invoke(null, self::FACEBOOK_GRAPH . '?' . http_build_query([
            'fields'       => 'id,name,email',
            'access_token' => $token['access_token'],
        ]));

        $providerId = (string) ($profile['id'] ?? '');
        if ($providerId === '') {
            throw new \RuntimeException('Facebook did not return a user id.');
        }

        $email = strtolower(trim((string) ($profile['email'] ?? '')));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $safeId = preg_replace('/[^0-9a-zA-Z]/', '', $providerId) ?: bin2hex(random_bytes(8));
            $email = 'fb' . $safeId . '@oauth.sportify.local';
        }

        return [
            'name'        => trim((string) ($profile['name'] ?? 'Sportify User')),
            'email'       => $email,
            'provider'    => 'facebook',
            'provider_id' => $providerId,
        ];
    }

    /** Meta data-deletion callback (POST signed_request). */
    public function dataDeletion(): string
    {
        header('Content-Type: application/json; charset=UTF-8');

        if (!SocialAuthService::isConfigured('facebook')) {
            http_response_code(503);
            return json_encode(['error' => 'Facebook login not configured'], JSON_UNESCAPED_SLASHES);
        }

        $signedRequest = (string) ($_POST['signed_request'] ?? '');
        if ($signedRequest === '') {
            http_response_code(400);
            return json_encode(['error' => 'Missing signed_request'], JSON_UNESCAPED_SLASHES);
        }

        $payload = self::parseFacebookSignedRequest($signedRequest, (string) \App\Core\App::config('social.facebook.app_secret', ''));
        if ($payload === null) {
            http_response_code(400);
            return json_encode(['error' => 'Invalid signed_request'], JSON_UNESCAPED_SLASHES);
        }

        $providerId = (string) ($payload['user_id'] ?? '');
        $code = bin2hex(random_bytes(8));
        $statusUrl = SocialAuthService::redirectUri('facebook') . '/status?code=' . urlencode($code);

        if ($providerId !== '') {
            error_log('[sportify] Facebook data-deletion request user_id=' . $providerId . ' code=' . $code);
        }

        return json_encode([
            'url'               => $statusUrl,
            'confirmation_code' => $code,
        ], JSON_UNESCAPED_SLASHES);
    }

    /** Status page linked from Meta data-deletion response. */
    public function dataDeletionStatus(): string
    {
        header('Content-Type: text/html; charset=UTF-8');
        $code = htmlspecialchars((string) ($_GET['code'] ?? ''), ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Data deletion · Sportify</title></head>'
            . '<body style="font-family:system-ui,sans-serif;max-width:640px;margin:40px auto;padding:0 16px">'
            . '<h1>Data deletion request received</h1>'
            . '<p>Your Facebook-linked data deletion request has been logged. '
            . 'To complete removal of personal data stored on Sportify, email '
            . '<a href="mailto:privacy@sportifyplus.de">privacy@sportifyplus.de</a> '
            . 'from the email on your Sportify account.</p>'
            . ($code !== '' ? '<p><small>Confirmation code: <code>' . $code . '</code></small></p>' : '')
            . '<p><a href="' . htmlspecialchars(route('data_deletion'), ENT_QUOTES, 'UTF-8') . '">Full data deletion instructions</a></p>'
            . '</body></html>';
    }

    /** @return array<string,mixed>|null */
    protected static function parseFacebookSignedRequest(string $signedRequest, string $secret): ?array
    {
        if ($secret === '' || !str_contains($signedRequest, '.')) {
            return null;
        }

        [$encodedSig, $payload] = explode('.', $signedRequest, 2);
        $sig = self::base64UrlDecode($encodedSig);
        $expected = hash_hmac('sha256', $payload, $secret, true);
        if (!hash_equals($expected, $sig)) {
            return null;
        }

        $data = json_decode(self::base64UrlDecode($payload), true);

        return is_array($data) ? $data : null;
    }

    protected static function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder > 0) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($input, '-_', '+/'), true);
    }
}
