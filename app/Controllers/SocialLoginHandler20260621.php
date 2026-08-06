<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\MemberProfile;
use App\Models\User;
use App\Models\CrmContactRepository;
use App\Services\DatabaseSetup;
use App\Services\GoogleAuthService;
use App\Services\MembershipService;
use App\Services\ReferralService;
use App\Services\SocialAuthService;
use App\Services\SocialAuthSettings;
use App\Support\OnlyTalentsContext;
use App\Support\Rbac;

/** Fresh OAuth callback handler (OPcache-safe deploy marker 2026-06-30). */
class SocialLoginHandler20260621 extends Controller
{
    public static function handleCallback(string $provider): void
    {
        try {
            $controller = new self();
            $response = match ($provider) {
                'google'   => $controller->googleCallback(),
                'facebook' => $controller->facebookCallback(),
                'linkedin' => $controller->linkedinCallback(),
                default    => $controller->redirect('/login'),
            };
            echo $response;
            if (trim($response) === '' && !headers_sent()) {
                $login = '/login?error=' . rawurlencode($provider === 'google' ? 'google' : 'oauth');
                $safe = htmlspecialchars($login, ENT_QUOTES, 'UTF-8');
                header('Location: ' . $login, true, 302);
                echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
                    . '<meta http-equiv="refresh" content="0;url=' . $safe . '">'
                    . '<title>Redirecting…</title></head><body>'
                    . '<p><a href="' . $safe . '">Continue to login</a></p></body></html>';
            }
        } catch (\Throwable $e) {
            @error_log('sportify oauth handleCallback/' . $provider . ': ' . $e->getMessage());
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['_flash']['error'] = $e->getMessage();
                session_write_close();
            }
            $login = '/login?error=' . rawurlencode($provider === 'google' ? 'google' : 'oauth');
            if (!headers_sent()) {
                header('Location: ' . $login, true, 302);
            }
            $safe = htmlspecialchars($login, ENT_QUOTES, 'UTF-8');
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
                . '<meta http-equiv="refresh" content="0;url=' . $safe . '">'
                . '<title>Redirecting…</title></head><body>'
                . '<p><a href="' . $safe . '">Continue to login</a></p></body></html>';
        }
    }

    public function google(): string
    {
        return $this->start('google', static fn (string $from, string $role) => SocialAuthService::googleAuthUrl($from, $role));
    }

    public function googleCallback(): string
    {
        return $this->finish('google', function (string $code, ?string $state) {
            return SocialAuthService::handleGoogleCallback($code, $state);
        });
    }

    public function facebook(): string
    {
        return $this->start('facebook', static fn (string $from, string $role) => SocialAuthService::facebookAuthUrl($from, $role));
    }

    public function facebookCallback(): string
    {
        return $this->finish('facebook', function (string $code, ?string $state) {
            return SocialAuthService::handleFacebookCallback($code, $state);
        });
    }

    public function linkedin(): string
    {
        return $this->start('linkedin', static fn (string $from, string $role) => SocialAuthService::linkedinAuthUrl($from, $role));
    }

    public function linkedinCallback(): string
    {
        return $this->finish('linkedin', function (string $code, ?string $state) {
            return SocialAuthService::handleLinkedInCallback($code, $state);
        });
    }

    protected function start(string $provider, callable $urlBuilder): string
    {
        if (Auth::check()) {
            return $this->redirect($this->safeHome());
        }

        $req = App::$request;
        $from = (string) $req->input('from', 'login');
        $role = (string) $req->input('role', 'player');
        $return = $this->captureOAuthReturn(
            (string) $req->input('next', ''),
            (string) $req->input('redirect', '')
        );

        if ($from === 'register') {
            $_SESSION['oauth_registering'] = true;
            $_SESSION['oauth_pending_role'] = $this->normalizeRole($role);
        } else {
            unset($_SESSION['oauth_registering'], $_SESSION['oauth_pending_role']);
        }
        if ($return !== '') {
            $_SESSION['oauth_return'] = $return;
        } else {
            unset($_SESSION['oauth_return']);
        }

        if (!SocialAuthSettings::isVisible($provider)) {
            flash('error', ucfirst($provider) . ' login is currently disabled.');
            return $this->redirect($this->returnRoute());
        }

        if (!SocialAuthService::isConfigured($provider)) {
            flash('error', ucfirst($provider) . ' login is not configured. ' . SocialAuthService::setupHint($provider));
            return $this->redirect($this->returnRoute());
        }

        try {
            // Avoid X-LiteSpeed-Purge — Hostinger can abort with empty HTTP 500.
            return $this->redirect($urlBuilder($from, $role));
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            return $this->redirect($this->returnRoute());
        }
    }

    protected function finish(string $provider, callable $handler): string
    {
        try {
            $req = App::$request;
            $error = (string) $req->input('error', '');
            if ($error !== '') {
                $desc = trim((string) $req->input('error_description', ''));
                $msg = ucfirst($provider) . ' sign-in was cancelled or denied.';
                if ($desc !== '') {
                    $msg .= ' ' . $desc;
                } elseif ($error !== 'access_denied') {
                    $msg .= ' (' . $error . ')';
                }
                flash('error', $msg);
                return $this->redirect($this->oauthFailureUrl($provider));
            }

            $code = (string) $req->input('code', '');
            $state = $req->input('state');
            if ($code === '') {
                flash('error', 'Missing authorization code from ' . $provider . '.');
                return $this->redirect($this->oauthFailureUrl($provider));
            }

            $profile = $handler($code, is_string($state) ? $state : null);
            return $this->loginOrRegister($profile);
        } catch (\Throwable $e) {
            @error_log('sportify oauth finish/' . $provider . ': ' . $e->getMessage());
            flash('error', $e->getMessage());
            return $this->redirect($this->oauthFailureUrl($provider));
        }
    }

    /** Safe post-OAuth failure URL — never depends on named routes being loaded. */
    protected function oauthFailureUrl(string $provider): string
    {
        try {
            return $this->returnRoute();
        } catch (\Throwable $e) {
            $q = $provider === 'google' ? 'error=google' : 'error=oauth';
            return '/login?' . $q;
        }
    }

    /** @param array{name:?string,email:string,provider:string,provider_id:string,google_id?:string,avatar_url?:?string} $profile */
    protected function loginOrRegister(array $profile): string
    {
        if (!DatabaseSetup::ensure()) {
            flash('error', 'Database is not ready. Check .env settings, then run: php sportify migrate');
            return $this->redirect(route('register'));
        }

        $isNew = false;
        if (($profile['provider'] ?? '') === 'google') {
            $role = 'player';
            if (!empty($_SESSION['oauth_registering'])) {
                $role = $this->normalizeRole((string) ($_SESSION['oauth_pending_role'] ?? 'player'));
            }

            try {
                $result = GoogleAuthService::findOrCreateUser($profile, $role);
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                return $this->redirect($this->oauthFailureUrl('google'));
            }

            $user = $result['user'];
            $isNew = $result['is_new'];
        } else {
            $user = User::findByEmail($profile['email']);
            $isNew = $user === null;

            if ($isNew) {
                $role = 'player';
                if (!empty($_SESSION['oauth_registering'])) {
                    $role = $this->normalizeRole((string) ($_SESSION['oauth_pending_role'] ?? 'player'));
                }

                $displayName = trim((string) ($profile['name'] ?? ''));
                if ($displayName === '') {
                    $displayName = 'Sportify User';
                }

                $id = User::createSocial([
                    'name'  => $displayName,
                    'email' => $profile['email'],
                    'role'  => $role,
                ]);

                if (!$id) {
                    flash('error', 'Could not create your account. Please try email registration.');
                    return $this->redirect(route('register'));
                }

                $user = User::find($id);
            }
        }

        // Keep the pre-OAuth session id so Hostinger/LiteSpeed reliably persists the login cookie.
        Auth::login((int) $user['id'], false);
        $ref = null;
        if (Database::available()) {
            MemberProfile::ensureForUser($user);
            if ($isNew) {
                MembershipService::ensureFreeMembership((int) $user['id'], (string) ($user['role'] ?? 'player'));
                CrmContactRepository::linkUserByEmail($profile['email'], (int) $user['id']);
                $ref = ReferralService::attributeRegistration((int) $user['id'], $profile['email']);
            }
        }

        $registering = !empty($_SESSION['oauth_registering']);
        unset($_SESSION['oauth_registering'], $_SESSION['oauth_pending_role']);

        if (!empty($_SESSION['pending_plan'])) {
            $tier = $_SESSION['pending_plan'];
            unset($_SESSION['pending_plan']);
            flash('success', 'Signed in! Complete your FIT-Pass checkout.');
            return $this->redirect(route('billing.checkout', ['tier' => $tier]));
        }

        if (!empty($_SESSION['pending_membership'])) {
            $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower((string) $_SESSION['pending_membership']));
            unset($_SESSION['pending_membership']);
            if ($slug !== '' && ($isNew || $registering)) {
                flash('success', __('membership.register_welcome'));
                return $this->redirect(route('membership.checkout', ['plan_slug' => $slug]));
            }
        }

        $return = $this->pullOAuthReturn();
        if ($return !== '') {
            if ($isNew || $registering) {
                $msg = !empty($ref['referrer_name'])
                    ? __('referrals.welcome_referred', ['name' => $ref['referrer_name']])
                    : 'Welcome to Sportify!';
                flash('success', $msg);
            } else {
                flash('success', 'Welcome back!');
            }
            return $this->redirect($return);
        }

        if ($isNew && $registering) {
            $msg = !empty($ref['referrer_name'])
                ? __('referrals.welcome_referred', ['name' => $ref['referrer_name']])
                : 'Welcome to Sportify! Complete your profile to connect with players and clubs.';
            flash('success', $msg);
            return $this->redirect($this->postAuthLandingUrl());
        }

        if ($isNew) {
            $msg = !empty($ref['referrer_name'])
                ? __('referrals.welcome_referred', ['name' => $ref['referrer_name']])
                : 'Account created. You can update your role in profile settings.';
            flash('success', $msg);
            return $this->redirect($this->postAuthLandingUrl());
        }

        flash('success', 'Welcome back!');
        return $this->redirect($this->safeHome());
    }

    /** Landing URL after OAuth — never send users to a gated route without an active session. */
    protected function postAuthLandingUrl(): string
    {
        return $this->safeHome();
    }

    protected function normalizeRole(string $role): string
    {
        $allowed = array_keys(Rbac::publicRegisterRoles());
        return in_array($role, $allowed, true) ? $role : 'player';
    }

    protected function returnRoute(): string
    {
        $route = !empty($_SESSION['oauth_registering']) ? route('register') : route('login');
        $next = $this->pullOAuthReturn(false);
        if ($next === '') {
            return $route;
        }
        $sep = str_contains($route, '?') ? '&' : '?';
        return $route . $sep . 'next=' . rawurlencode($next);
    }

    protected function home(): string
    {
        return Auth::homeUrl();
    }

    /** Post-auth landing that never sends users to a gated route. */
    protected function safeHome(): string
    {
        $route = Auth::homeRoute();
        if (Rbac::canAccessRoute($route)) {
            $params = Auth::homeRouteParams();
            return $params !== [] ? route($route, $params) : route($route);
        }

        return route('profile.edit');
    }

    /** Onboarding after social signup — profile edit is always safe (auth-only). */
    protected function onboardingUrl(): string
    {
        $role = Auth::role() ?? 'player';
        $memRole = MembershipService::resolveRole($role);
        if (Auth::can('profile.edit') && Rbac::canAccessRoute('membership.role')) {
            return route('membership.role', ['role' => $memRole]);
        }

        return route('profile.edit');
    }

    protected function captureOAuthReturn(string $next, string $legacyRedirect = ''): string
    {
        $redirect = trim($next !== '' ? $next : $legacyRedirect);

        return OnlyTalentsContext::sanitizeSameHostReturnUrl($redirect);
    }

    protected function pullOAuthReturn(bool $clear = true): string
    {
        $return = (string) ($_SESSION['oauth_return'] ?? '');
        if ($clear) {
            unset($_SESSION['oauth_return']);
        }

        return OnlyTalentsContext::sanitizeSameHostReturnUrl($return);
    }
}
