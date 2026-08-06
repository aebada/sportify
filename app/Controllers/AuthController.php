<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\User;
use App\Models\MemberProfile;
use App\Models\CrmContactRepository;
use App\Services\DatabaseSetup;
use App\Services\ReferralService;
use App\Services\TransferBureauService;
use App\Services\MembershipService;
use App\Models\TransferListingRepository;
use App\Models\TransferSeekerRepository;
use App\Models\TrainerProfileRepository;
use App\Support\OnlyTalentsContext;
use App\Support\Rbac;

class AuthController extends Controller
{
    public function showLogin(): string
    {
        if (Auth::check()) {
            $redirect = $this->resolveReturnTarget();
            return $this->redirect($redirect !== '' ? $redirect : $this->home());
        }
        $redirect = $this->resolveReturnTarget();
        if ($redirect !== '') {
            $_SESSION['_login_redirect'] = $redirect;
        }
        $dbReady = Database::available() && Database::tableExists('users');
        $html = $this->view('auth.login', [
            'title' => 'Log in',
            'dbReady' => $dbReady,
            'next' => $redirect,
        ], 'auth-v2');

        return $this->ensureAuthReturnInHtml($html, $redirect);
    }

    public function login(): string
    {
        $req = App::$request;
        $postedReturn = $this->safeRedirect((string) $req->input('next', ''));
        if ($postedReturn === '') {
            $postedReturn = $this->safeRedirect((string) $req->input('redirect', ''));
        }
        if ($postedReturn !== '') {
            $_SESSION['_login_redirect'] = $postedReturn;
        }
        if (!Csrf::verify($req->input('_csrf'))) {
            return $this->fail('login', 'Session expired, please try again.');
        }

        if (!$this->authStorageReady()) {
            return $this->fail(
                'login',
                'Sign-in is unavailable right now. Check .env database settings on the server, then run: php sportify fresh'
            );
        }

        $email = (string) $req->input('email');
        $password = (string) $req->input('password');

        if (Auth::attempt($email, $password)) {
            $redirect = $this->safeRedirect((string) ($_SESSION['_login_redirect'] ?? ''));
            unset($_SESSION['_login_redirect']);
            return $this->redirect($redirect !== '' ? $redirect : $this->home(), 303);
        }
        $_SESSION['_old']['email'] = $email;
        return $this->fail('login', 'Invalid email or password.');
    }

    public function showRegister(): string
    {
        if (Auth::check()) {
            $redirect = $this->resolveReturnTarget();
            return $this->redirect($redirect !== '' ? $redirect : $this->home());
        }
        $next = $this->resolveReturnTarget();
        if ($next !== '') {
            $_SESSION['_register_redirect'] = $next;
        }
        if (!empty($_GET['plan'])) {
            $_SESSION['pending_plan'] = preg_replace('/[^a-z0-9]/', '', strtolower((string) $_GET['plan']));
        }
        if (!empty($_GET['membership'])) {
            $_SESSION['pending_membership'] = preg_replace('/[^a-z0-9\-]/', '', strtolower((string) $_GET['membership']));
        }
        $dbReady = Database::available() && Database::tableExists('users');
        $html = $this->view('auth.register', [
            'title' => 'Create account',
            'roles' => $this->roles(),
            'pendingPlan' => $_SESSION['pending_plan'] ?? null,
            'dbReady' => $dbReady,
            'referralCode' => ReferralService::currentCode(),
            'next' => $next,
        ], 'auth-v2');

        return $this->ensureAuthReturnInHtml($html, $next);
    }

    public function register(): string
    {
        $req = App::$request;
        $postedReturn = $this->safeRedirect((string) $req->input('next', ''));
        if ($postedReturn === '') {
            $postedReturn = $this->safeRedirect((string) $req->input('redirect', ''));
        }
        if ($postedReturn !== '') {
            $_SESSION['_register_redirect'] = $postedReturn;
        }
        if (!Csrf::verify($req->input('_csrf'))) {
            return $this->fail('register', 'Session expired, please try again.');
        }

        $data = $req->only(['name', 'email', 'password', 'password_confirm', 'role', 'seeking_club', 'position', 'city', 'club_team_name', 'club_team_level', 'coaching_license', 'years_experience', 'current_club']);
        $data['specialties'] = $req->input('specialties', []);
        $errors = $this->validateRegister($data);

        if ($errors) {
            $_SESSION['_old'] = ['name' => $data['name'], 'email' => $data['email'], 'role' => $data['role']];
            flash('errors', $errors);
            flash('error', 'Please fix the errors below.');
            return $this->redirect($this->routeWithNext('register', $this->registerNext()));
        }

        if (!$this->authStorageReady()) {
            return $this->fail(
                'register',
                __('auth.db_not_ready')
            );
        }

        if (!DatabaseSetup::ensure()) {
            return $this->fail(
                'register',
                'Database is not ready. Check .env settings, then run: php sportify migrate'
            );
        }

        $id = User::create([
            'name'     => trim($data['name']),
            'email'    => $data['email'],
            'password' => $data['password'],
            'role'     => $data['role'],
        ]);

        if (!$id) {
            return $this->fail('register', 'Could not create account. Is the database configured? Run: php sportify migrate && php sportify seed');
        }

        Auth::login($id);
        MembershipService::ensureFreeMembership($id, $data['role']);
        $ref = null;
        if (Database::available()) {
            MemberProfile::ensureForUser(User::find($id));
            $this->setupTransferBureauOnRegister($id, $data);
            CrmContactRepository::linkUserByEmail($data['email'], $id);
            $ref = ReferralService::attributeRegistration($id, $data['email']);
            if ($ref) {
                flash('referral_welcome', $ref);
            }
        }

        if (!empty($_SESSION['pending_plan'])) {
            $tier = $_SESSION['pending_plan'];
            unset($_SESSION['pending_plan']);
            flash('success', 'Account created! Complete your FIT-Pass checkout.');
            return $this->redirect(route('billing.checkout', ['tier' => $tier]));
        }

        if (!empty($_GET['membership']) || !empty($_SESSION['pending_membership'])) {
            $slug = $_SESSION['pending_membership'] ?? preg_replace('/[^a-z0-9\-]/', '', strtolower((string) ($_GET['membership'] ?? '')));
            unset($_SESSION['pending_membership']);
            if ($slug) {
                flash('success', __('membership.register_welcome'));
                return $this->redirect(route('membership.checkout', ['plan_slug' => $slug]));
            }
        }

        $welcome = __('membership.register_suggest');
        if (!empty($ref['referrer_name'])) {
            $welcome = __('referrals.welcome_referred', ['name' => $ref['referrer_name']]) . ' ' . $welcome;
        }
        flash('success', $welcome);
        $next = $this->registerNext();
        unset($_SESSION['_register_redirect']);
        if ($next !== '') {
            return $this->redirect($next);
        }
        return $this->redirect($this->home());
    }

    public function logout(): string
    {
        Auth::logout();
        return $this->redirect(route('home'));
    }

    public function showForgot(): string
    {
        return $this->ensureGoogleBtnInHtml($this->ensureAuthBrandInHtml($this->view('auth.forgot', ['title' => 'Reset password'], 'auth-v2')));
    }

    public function sendReset(): string
    {
        $req = App::$request;
        $email = (string) $req->input('email');
        $user = User::findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(24));
            User::setResetToken($email, $token);
            // On shared hosting without mail config, surface the link for testing.
            $link = route('password.reset', ['token' => $token]);
            flash('reset_link', $link);
        }
        flash('success', 'If that email exists, a reset link has been generated.');
        return $this->redirect(route('password.request'));
    }

    public function showReset(string $token): string
    {
        return $this->ensureGoogleBtnInHtml($this->ensureAuthBrandInHtml($this->view('auth.reset', ['title' => 'Set new password', 'token' => $token], 'auth-v2')));
    }

    public function resetPassword(): string
    {
        $req = App::$request;
        $token = (string) $req->input('token');
        $password = (string) $req->input('password');
        $confirm = (string) $req->input('password_confirm');

        if (strlen($password) < 8 || $password !== $confirm) {
            flash('error', 'Passwords must match and be at least 8 characters.');
            return $this->redirect(route('password.reset', ['token' => $token]));
        }

        $user = User::findByResetToken($token);
        if (!$user) {
            flash('error', 'Invalid or expired reset link.');
            return $this->redirect(route('password.request'));
        }

        User::updatePassword((int) $user['id'], $password);
        flash('success', 'Password updated. You can now log in.');
        return $this->redirect(route('login'));
    }

    // ---- helpers ----
    protected function validateRegister(array $d): array
    {
        $e = [];
        if (trim((string) $d['name']) === '') {
            $e['name'] = 'Name is required.';
        }
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            $e['email'] = 'A valid email is required.';
        } elseif ($this->authStorageReady() && User::findByEmail($d['email'])) {
            $e['email'] = 'That email is already registered.';
        }
        if (strlen((string) $d['password']) < 8) {
            $e['password'] = 'Password must be at least 8 characters.';
        } elseif ($d['password'] !== $d['password_confirm']) {
            $e['password_confirm'] = 'Passwords do not match.';
        }
        if (!in_array($d['role'], array_keys($this->roles()), true)) {
            $e['role'] = 'Please choose a role.';
        }
        return $e;
    }

    protected function roles(): array
    {
        return Rbac::publicRegisterRoles();
    }

    protected function home(): string
    {
        return Auth::homeUrl();
    }

    protected function safeRedirect(string $url): string
    {
        return OnlyTalentsContext::sanitizeSameHostReturnUrl($url);
    }

    protected function resolveReturnTarget(): string
    {
        $req = App::$request;
        $next = $this->safeRedirect((string) $req->input('next', ''));
        if ($next !== '') {
            return $next;
        }
        $legacy = $this->safeRedirect((string) $req->input('redirect', ''));
        if ($legacy !== '') {
            return $legacy;
        }
        return '';
    }

    protected function routeWithNext(string $routeName, string $next): string
    {
        $base = route($routeName);
        if ($next === '') {
            return $base;
        }
        $sep = str_contains($base, '?') ? '&' : '?';
        $encoded = rawurlencode($next);

        return $base . $sep . 'next=' . $encoded . '&redirect=' . $encoded;
    }


    /** Ensure Sportify logo top-left on auth pages (survives stale layout opcache). */
    protected function ensureAuthBrandInHtml(string $html): string
    {
        if (str_contains($html, 'auth-brand') || str_contains($html, 'deploy-marker:20260720-auth-brand-topleft-v1')) {
            return $html;
        }

        $brand = <<<'HTML'
<!-- deploy-marker:20260720-auth-brand-topleft-v1 -->
<style>
.auth-body{position:relative}
.auth-brand{position:absolute;top:20px;inset-inline-start:24px;z-index:5;display:inline-flex;align-items:center;line-height:0}
.auth-brand .brand-logo,.auth-brand .brand-logo-img{height:36px;width:auto;display:block;object-fit:contain;filter:drop-shadow(0 1px 2px rgba(0,0,0,.35));-webkit-filter:drop-shadow(0 1px 2px rgba(0,0,0,.35))}
@media (min-width:981px){.auth-brand{display:none}}
@media (max-width:980px){.auth-main{padding-top:72px!important}}
</style>
<a href="/" class="auth-brand brand" aria-label="Home">
<picture><source type="image/webp" srcset="/assets/img/logo.webp"><img src="/assets/img/logo.png" alt="Sportify" class="brand-logo brand-logo-img" width="136" height="36" decoding="async" fetchpriority="high"></picture>
</a>
HTML;

        if (preg_match('/<body([^>]*)>/i', $html)) {
            $html = preg_replace('/<body([^>]*)>/i', '<body$1>' . $brand, $html, 1);
        } else {
            $html = $brand . $html;
        }

        return $html;
    }

    /** Fix raw i18n key + inject google-btn styles when lang/views are stale on server. */
    protected function ensureGoogleBtnInHtml(string $html): string
    {
        $label = match (function_exists('locale') ? locale() : 'en') {
            'de' => 'Mit Google fortfahren',
            'ar' => 'المتابعة عبر Google',
            default => 'Continue with Google',
        };

        // Always rewrite raw i18n key (stale lang/opcache on Hostinger).
        if (str_contains($html, 'auth.social.continue_google')) {
            $html = str_replace('auth.social.continue_google', $label, $html);
        }

        // If google-btn link exists but label still missing / empty after SVG, force label text.
        if (preg_match('#(<a[^>]*class="[^"]*google-btn[^"]*"[^>]*>)(.*?)(</a>)#is', $html, $m)) {
            $inner = $m[2];
            if (!str_contains($inner, 'Continue with Google')
                && !str_contains($inner, 'Mit Google fortfahren')
                && !str_contains($inner, 'المتابعة عبر Google')) {
                // Keep SVG; replace trailing text with hardcoded label.
                $newInner = preg_replace('#</svg>\s*\K.*#s', ' ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8'), $inner, 1);
                if (is_string($newInner) && $newInner !== '') {
                    $html = str_replace($m[0], $m[1] . $newInner . $m[3], $html);
                }
            }
        }

        if (str_contains($html, 'google-btn') && !str_contains($html, 'google-btn-v1')) {
            $css = '<style>/* google-btn-v1 */.google-btn{position:relative;width:100%;display:inline-flex;align-items:center;justify-content:center;gap:.75rem;padding:.875rem 1.25rem;border:1px solid #d0d7de;border-radius:10px;background:#fff;color:#1f2328;font-size:.9375rem;font-weight:600;font-family:inherit;text-decoration:none;box-sizing:border-box;cursor:pointer;transition:border-color .2s,box-shadow .2s}.google-btn:hover:not(.google-btn--soon):not([aria-disabled="true"]){border-color:#dadce0;box-shadow:0 1px 3px rgba(60,64,67,.2)}.google-btn--soon{cursor:not-allowed;opacity:.92}.google-btn__icon{flex-shrink:0;display:block}</style>';
            if (preg_match('/<\/head>/i', $html)) {
                $html = preg_replace('/<\/head>/i', $css . '</head>', $html, 1);
            } else {
                $html = $css . $html;
            }
        }

        return $html;
    }

    /** Inject return-url fields when a stale view/opcache omitted them. */
    protected function ensureAuthReturnInHtml(string $html, string $returnTarget): string
    {
        // Always ensure top-left brand + Google button on auth HTML (even when no redirect target).
        $html = $this->ensureAuthBrandInHtml($html);
        $html = $this->ensureGoogleBtnInHtml($html);

        $returnTarget = $this->safeRedirect($returnTarget);
        if ($returnTarget === '') {
            return $html;
        }

        if (!str_contains($html, 'name="next"') && !str_contains($html, "name='next'")) {
            $escaped = htmlspecialchars($returnTarget, ENT_QUOTES, 'UTF-8');
            $hidden = '<input type="hidden" name="next" value="' . $escaped . '">'
                . '<input type="hidden" name="redirect" value="' . $escaped . '">';
            if (preg_match('/(<input[^>]*name=(["\'])_csrf\2[^>]*>)/i', $html, $m)) {
                $html = str_replace($m[1], $m[1] . $hidden, $html);
            } elseif (preg_match('/(<form[^>]*method=(["\'])post\2[^>]*>)/i', $html, $m)) {
                $html = str_replace($m[1], $m[1] . $hidden, $html);
            }
        }

        $encoded = rawurlencode($returnTarget);
        $html = preg_replace_callback(
            '/data-auth-url="(\/auth\/(google|facebook|linkedin)\?[^"]*)"/',
            static function (array $m) use ($encoded): string {
                $url = $m[1];
                if (str_contains($url, 'next=') || str_contains($url, 'redirect=')) {
                    return $m[0];
                }
                $sep = str_contains($url, '?') ? '&' : '?';

                return 'data-auth-url="' . $url . $sep . 'next=' . $encoded . '&redirect=' . $encoded . '"';
            },
            $html
        );

        return $html;
    }

    protected function registerNext(): string
    {
        return $this->safeRedirect((string) ($_SESSION['_register_redirect'] ?? ''));
    }

    protected function fail(string $view, string $message): string
    {
        flash('error', $message);
        $next = $view === 'register'
            ? $this->registerNext()
            : $this->safeRedirect((string) ($_SESSION['_login_redirect'] ?? ''));
        return $this->redirect($this->routeWithNext($view, $next));
    }

    protected function authStorageReady(): bool
    {
        return Database::available() && Database::tableExists('users');
    }

    protected function setupTransferBureauOnRegister(int $userId, array $data): void
    {
        if (!Database::tableExists('transfer_seekers')) {
            return;
        }
        $role = $data['role'] ?? 'player';
        $city = trim((string) ($data['city'] ?? ''));
        $coords = $city !== '' ? TransferBureauService::coordsForCity($city) : null;
        $now = date('Y-m-d H:i:s');

        if ($role === 'club') {
            $teamName = trim((string) ($data['club_team_name'] ?? ''));
            $teamLevel = (string) ($data['club_team_level'] ?? '1_herren');
            Database::execute(
                'UPDATE member_profiles SET club_team_name = ?, club_team_level = ?, city = ?, latitude = ?, longitude = ?, updated_at = ? WHERE user_id = ?',
                [$teamName ?: null, $teamLevel, $city ?: null, $coords['lat'] ?? null, $coords['lng'] ?? null, $now, $userId]
            );
            if ($teamName !== '' && $city !== '') {
                TransferListingRepository::create([
                    'club_user_id'     => $userId,
                    'team_name'        => $teamName,
                    'team_level'       => $teamLevel,
                    'gender'           => 'men',
                    'positions_needed' => [],
                    'league_level'     => 'amateur',
                    'city'             => $city,
                    'lat'              => $coords['lat'] ?? null,
                    'lng'              => $coords['lng'] ?? null,
                    'description'      => 'Neu registrierter Verein — Spieler gesucht.',
                ]);
            }
            return;
        }

        if ($role === 'player' && !empty($data['seeking_club'])) {
            $position = trim((string) ($data['position'] ?? ''));
            $positions = $position !== '' ? [$position] : [];
            Database::execute(
                'UPDATE member_profiles SET seeking_club = 1, city = ?, latitude = ?, longitude = ?, updated_at = ? WHERE user_id = ?',
                [$city ?: null, $coords['lat'] ?? null, $coords['lng'] ?? null, $now, $userId]
            );
            TransferSeekerRepository::upsert($userId, [
                'type'         => 'player',
                'positions'    => $positions,
                'city'         => $city,
                'lat'          => $coords['lat'] ?? null,
                'lng'          => $coords['lng'] ?? null,
                'availability' => 'immediate',
                'bio'          => 'Suche einen Verein.',
                'status'       => 'seeking',
            ]);
        }

        if ($role === 'trainer') {
            $user = User::find($userId);
            if ($user) {
                TrainerProfileRepository::ensureForUser($user);
                TrainerProfileRepository::update($userId, $data);
            }
            if ($city !== '') {
                Database::execute(
                    'UPDATE member_profiles SET city = ?, latitude = ?, longitude = ?, updated_at = ? WHERE user_id = ?',
                    [$city, $coords['lat'] ?? null, $coords['lng'] ?? null, $now, $userId]
                );
            }
            TransferSeekerRepository::upsert($userId, [
                'type'         => 'trainer',
                'positions'    => ['TRAINER'],
                'city'         => $city,
                'lat'          => $coords['lat'] ?? null,
                'lng'          => $coords['lng'] ?? null,
                'availability' => 'immediate',
                'bio'          => trim((string) ($data['coaching_license'] ?? '')) !== ''
                    ? 'Trainer — ' . trim((string) $data['coaching_license'])
                    : 'Trainer auf der Suche nach einem Verein.',
                'status'       => 'seeking',
            ]);
        }
    }
}
