<?php

namespace App\Core;

use App\Models\User;
use App\Models\SubscriptionRepository;
use App\Support\Rbac;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return User::find((int) $_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return self::check() ? (int) $_SESSION['user_id'] : null;
    }

    public static function role(): ?string
    {
        $u = self::user();
        if (!$u || empty($u['role'])) {
            return null;
        }
        return Rbac::resolveStoredRole((string) $u['role']);
    }

    public static function isSuperAdmin(): bool
    {
        return self::hasRole('super_admin');
    }

    public static function isAdmin(): bool
    {
        return self::hasAnyRole(['admin', 'super_admin']);
    }

    /** @param string|array<int,string> $roles */
    public static function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return self::hasAnyRole($roles);
        }
        return Rbac::hasRole(self::role(), $roles);
    }

    /** @param string|array<int,string> $roles */
    public static function hasAnyRole(string|array $roles): bool
    {
        return Rbac::hasAnyRole(self::role(), $roles);
    }

    public static function can(string $permission): bool
    {
        $role = self::role();
        if (!Rbac::can($role, $permission)) {
            return false;
        }
        $feature = Rbac::membershipFeatureForPermission($permission);
        if ($feature !== null && self::check()) {
            return self::memberFeature($feature);
        }
        return true;
    }

    public static function homeRoute(): string
    {
        $role = self::role();
        if (!$role) {
            return 'home';
        }
        return Rbac::homeRoute($role);
    }

    /** @return array<string, mixed> */
    public static function homeRouteParams(): array
    {
        $role = self::role();
        if (!$role) {
            return [];
        }
        return Rbac::homeRouteParams($role);
    }

    public static function homeUrl(): string
    {
        $params = self::homeRouteParams();
        return $params !== [] ? route(self::homeRoute(), $params) : route(self::homeRoute());
    }

    public static function subscription(): ?array
    {
        if (!self::check() || !Database::available()) {
            return null;
        }
        return SubscriptionRepository::activeForUser((int) $_SESSION['user_id']);
    }

    /** @param string|array<int,string> $tiers */
    public static function hasPlan(string|array $tiers): bool
    {
        $sub = self::subscription();
        if (!$sub) {
            return false;
        }
        $tiers = (array) $tiers;
        return in_array($sub['tier'] ?? '', $tiers, true);
    }

    public static function planTier(): ?string
    {
        return self::subscription()['tier'] ?? null;
    }

    public static function userMembership(): ?array
    {
        if (!self::check() || !Database::available()) {
            return null;
        }
        return \App\Services\MembershipService::membershipForUser((int) $_SESSION['user_id'], self::role());
    }

    public static function memberFeature(string $feature): bool
    {
        if (!self::check()) {
            return false;
        }
        $user = self::user();
        return \App\Services\MembershipService::hasFeature($user, $feature);
    }

    public static function memberLimit(string $limitKey): array
    {
        if (!self::check()) {
            return ['allowed' => false, 'limit' => 0, 'used' => 0, 'remaining' => 0, 'unlimited' => false];
        }
        return \App\Services\MembershipService::checkLimit(self::user(), $limitKey);
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            self::login((int) $user['id']);
            return true;
        }
        return false;
    }

    public static function login(int $userId, bool $regenerateSession = true): void
    {
        if ($regenerateSession) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = $userId;
        self::reissueSessionCookie();
    }

    /** Re-send PHPSESSID so Hostinger/LiteSpeed persists login after OAuth round-trip. */
    protected static function reissueSessionCookie(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE || headers_sent()) {
            return;
        }
        $sessionId = session_id();
        if ($sessionId === '') {
            return;
        }
        $params = session_get_cookie_params();
        setcookie(session_name(), $sessionId, [
            'expires'  => ($params['lifetime'] ?? 0) > 0 ? time() + (int) $params['lifetime'] : 0,
            'path'     => ($params['path'] ?? '') !== '' ? $params['path'] : '/',
            'domain'   => $params['domain'] ?? '',
            'secure'   => (bool) ($params['secure'] ?? false),
            'httponly' => (bool) ($params['httponly'] ?? true),
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        session_regenerate_id(true);
    }
}
