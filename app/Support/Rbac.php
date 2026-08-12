<?php

namespace App\Support;

class Rbac
{
    protected static ?array $config = null;

    /** @var array<string, array<int, string>> */
    protected static array $permissionCache = [];

    public static function config(): array
    {
        if (self::$config === null) {
            self::$config = require dirname(__DIR__, 2) . '/config/roles.php';
        }
        return self::$config;
    }

    public static function roles(): array
    {
        return self::config()['roles'] ?? [];
    }

    public static function roleMeta(string $role): ?array
    {
        $role = self::resolveStoredRole($role);
        return self::roles()[$role] ?? null;
    }

    public static function roleLabel(string $role): string
    {
        $role = self::resolveStoredRole($role);
        $meta = self::roleMeta($role);
        if ($meta) {
            $key = 'roles.' . $role;
            $translated = \App\Core\Lang::get($key);
            if ($translated !== $key) {
                return $translated;
            }
            return (string) ($meta['label'] ?? $role);
        }
        return ucfirst(str_replace('_', ' ', $role));
    }

    public static function roleBadgeClass(string $role): string
    {
        $role = self::resolveStoredRole($role);
        return match ($role) {
            'super_admin' => 'chip chip--red',
            'admin'       => 'chip chip--orange',
            'marketing'   => 'chip chip--blue',
            'scout'       => 'chip chip--green',
            'club'        => 'chip chip--purple',
            'player'      => 'chip chip--teal',
            'provider'    => 'chip chip--gold',
            'trainer'     => 'chip chip--amber',
            'fan'         => 'chip chip--pink',
            default       => 'chip',
        };
    }

    public static function publicRegisterRoles(): array
    {
        $cfg = self::config();
        $allowed = $cfg['public_register'] ?? [];
        $roles = self::roles();
        $out = [];
        foreach ($allowed as $key) {
            if (isset($roles[$key])) {
                $out[$key] = ['label' => self::roleLabel($key)];
            }
        }
        return $out;
    }

    public static function allRoleKeys(): array
    {
        return array_keys(array_filter(
            self::roles(),
            static fn(string $key): bool => $key !== 'guest',
            ARRAY_FILTER_USE_KEY
        ));
    }

    public static function elevatedRoles(): array
    {
        return self::config()['elevated_roles'] ?? ['super_admin', 'admin'];
    }

    public static function legacyAliases(): array
    {
        return self::config()['legacy_aliases'] ?? [];
    }

    /** Normalize a stored users.role value (legacy aliases → canonical slug). */
    public static function resolveStoredRole(?string $role): ?string
    {
        if (!$role) {
            return null;
        }
        $aliases = self::legacyAliases();
        if (isset($aliases[$role])) {
            $role = $aliases[$role];
        }
        return $role;
    }

    public static function normalizeRole(string $role): string
    {
        $role = self::resolveStoredRole($role) ?? $role;
        return isset(self::roles()[$role]) && $role !== 'guest' ? $role : 'player';
    }

    public static function homeRoute(string $role): string
    {
        $meta = self::roleMeta($role);
        return $meta['dashboard_redirect'] ?? $meta['home_route'] ?? 'home';
    }

    /** @return array<string, mixed> */
    public static function homeRouteParams(string $role): array
    {
        $meta = self::roleMeta($role);
        return $meta['home_route_params'] ?? [];
    }

    public static function permissionsForRole(?string $role, bool $visited = false): array
    {
        if (!$role) {
            return [];
        }
        $role = self::resolveStoredRole($role) ?? $role;
        if (isset(self::$permissionCache[$role])) {
            return self::$permissionCache[$role];
        }
        $meta = self::roles()[$role] ?? null;
        if (!$meta) {
            return [];
        }
        $perms = $meta['permissions'] ?? [];
        if (!$visited) {
            foreach ($meta['inherits'] ?? [] as $parent) {
                $parent = self::resolveStoredRole($parent) ?? $parent;
                $perms = array_merge($perms, self::permissionsForRole($parent, true));
            }
        }
        $perms = array_values(array_unique($perms));
        if (!$visited) {
            self::$permissionCache[$role] = $perms;
        }
        return $perms;
    }

    public static function can(?string $role, string $permission): bool
    {
        if (!$role) {
            return false;
        }
        $role = self::resolveStoredRole($role) ?? $role;
        $perms = self::permissionsForRole($role);
        if (in_array('*', $perms, true)) {
            return true;
        }
        return in_array($permission, $perms, true);
    }

    public static function membershipFeatureForPermission(string $permission): ?string
    {
        $map = self::config()['membership_gated_permissions'] ?? [];
        $feature = $map[$permission] ?? null;
        return is_string($feature) && $feature !== '' ? $feature : null;
    }

    /** Named routes that must stay public (OAuth, login, register). */
    public static function isPublicRoute(string $routeName): bool
    {
        if ($routeName === '') {
            return false;
        }
        if (in_array($routeName, self::publicRouteNames(), true)) {
            return true;
        }
        foreach (['auth.google', 'auth.facebook', 'auth.linkedin'] as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }
        return false;
    }

    /** @return list<string> */
    public static function publicRouteNames(): array
    {
        return [
            'home',
            'login',
            'login.post',
            'register',
            'register.post',
            'password.request',
            'password.email',
            'password.reset',
            'password.update',
            'lang.switch',
            'locale.switch',
            'auth.google',
            'auth.google.callback',
            'auth.facebook',
            'auth.facebook.callback',
            'auth.facebook.data_deletion',
            'auth.facebook.data_deletion.status',
            'auth.linkedin',
            'auth.linkedin.callback',
        ];
    }

    public static function routePermission(string $routeName): ?string
    {
        if ($routeName === '' || self::isPublicRoute($routeName)) {
            return null;
        }
        $perm = self::config()['route_permissions'][$routeName] ?? null;
        return is_string($perm) && $perm !== '' ? $perm : null;
    }

    /** True when the current user may open the named route (or route is public). */
    public static function canAccessRoute(string $routeName): bool
    {
        if (self::isPublicRoute($routeName)) {
            return true;
        }
        $perm = self::config()['route_permissions'][$routeName] ?? null;
        if (!is_string($perm) || $perm === '') {
            return true;
        }
        return \App\Core\Auth::check() && \App\Core\Auth::can($perm);
    }

    public static function hasRole(?string $userRole, string $required): bool
    {
        if (!$userRole) {
            return false;
        }
        $userRole = self::resolveStoredRole($userRole) ?? $userRole;
        $required = self::resolveStoredRole($required) ?? $required;
        return $userRole === $required;
    }

    /** @param string|array<int,string> $roles */
    public static function hasAnyRole(?string $userRole, string|array $roles): bool
    {
        if (!$userRole) {
            return false;
        }
        foreach ((array) $roles as $r) {
            if (self::hasRole($userRole, $r)) {
                return true;
            }
        }
        return false;
    }

    public static function canAssignRole(?string $actorRole, string $targetRole): bool
    {
        if (!$actorRole) {
            return false;
        }
        $actorRole = self::resolveStoredRole($actorRole) ?? $actorRole;
        $targetRole = self::normalizeRole($targetRole);
        if ($targetRole === 'guest') {
            return false;
        }
        if ($actorRole === 'super_admin') {
            return isset(self::roles()[$targetRole]);
        }
        if ($actorRole === 'admin') {
            return !in_array($targetRole, self::elevatedRoles(), true);
        }
        return false;
    }

    public static function isElevatedRole(string $role): bool
    {
        $role = self::resolveStoredRole($role) ?? $role;
        return in_array($role, self::elevatedRoles(), true);
    }
}
