<?php

namespace App\Models;

use App\Core\Database;
use App\Support\Rbac;

class User
{
    public const ROLES = [
        'super_admin', 'admin', 'marketing', 'player', 'club', 'scout', 'agent', 'coach',
        'fan', 'company', 'provider',
    ];

    public static function find(int $id): ?array
    {
        return Database::first('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::first('SELECT * FROM users WHERE email = ?', [strtolower($email)]);
    }

    /** Socialite-style lookup by Google subject id (users.google_id column). */
    public static function findByGoogleId(string $googleId): ?array
    {
        $googleId = trim($googleId);
        if ($googleId === '' || !Database::available()) {
            return null;
        }
        if (!Database::columnExists('users', 'google_id')) {
            return null;
        }

        return Database::first('SELECT * FROM users WHERE google_id = ? LIMIT 1', [$googleId]);
    }

    public static function setGoogleId(int $id, string $googleId): bool
    {
        $googleId = trim($googleId);
        if ($id < 1 || $googleId === '' || !Database::available()) {
            return false;
        }
        if (!Database::columnExists('users', 'google_id')) {
            return false;
        }

        return Database::execute('UPDATE users SET google_id = ? WHERE id = ?', [$googleId, $id]);
    }

    public static function create(array $data): ?int
    {
        $role = Rbac::normalizeRole((string) ($data['role'] ?? 'player'));
        $ok = Database::execute(
            'INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?)',
            [
                $data['name'],
                strtolower($data['email']),
                password_hash($data['password'], PASSWORD_DEFAULT),
                $role,
                date('Y-m-d H:i:s'),
            ]
        );
        if (!$ok) {
            return null;
        }
        $id = (int) Database::lastInsertId();
        if ($id && Database::tableExists('referral_codes')) {
            ReferralCodeRepository::ensureForUser($id, $data['name']);
        }
        return $id;
    }

    /** OAuth users get a random password hash (use password reset to set one). */
    public static function createSocial(array $data): ?int
    {
        $role = Rbac::normalizeRole((string) ($data['role'] ?? 'player'));
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            $name = 'Sportify User';
        }
        $ok = Database::execute(
            'INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?)',
            [
                $name,
                strtolower($data['email']),
                password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
                $role,
                date('Y-m-d H:i:s'),
            ]
        );
        if (!$ok) {
            return null;
        }
        $id = (int) Database::lastInsertId();
        if ($id && Database::tableExists('referral_codes')) {
            ReferralCodeRepository::ensureForUser($id, $name);
        }
        return $id;
    }

    public static function updateName(int $id, string $name): bool
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }

        return Database::execute('UPDATE users SET name = ? WHERE id = ?', [$name, $id]);
    }

    public static function all(?string $roleFilter = null): array
    {
        if ($roleFilter && $roleFilter !== 'all') {
            return Database::select(
                'SELECT id, name, email, role, created_at FROM users WHERE role = ? ORDER BY id DESC',
                [$roleFilter]
            );
        }
        return Database::select('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC');
    }

    public static function count(?string $role = null): int
    {
        if ($role) {
            $row = Database::first('SELECT COUNT(*) AS c FROM users WHERE role = ?', [$role]);
        } else {
            $row = Database::first('SELECT COUNT(*) AS c FROM users');
        }
        return (int) ($row['c'] ?? 0);
    }

    public static function updateRole(int $id, string $role): bool
    {
        $role = Rbac::normalizeRole($role);
        return Database::execute('UPDATE users SET role = ? WHERE id = ?', [$role, $id]);
    }

    public static function hasSuperAdmin(): bool
    {
        return self::countSuperAdmins() > 0;
    }

    public static function countSuperAdmins(): int
    {
        if (!Database::available()) {
            return 0;
        }
        $row = Database::first("SELECT COUNT(*) AS c FROM users WHERE role = 'super_admin'");
        return (int) ($row['c'] ?? 0);
    }

    public static function setResetToken(string $email, string $token): bool
    {
        return Database::execute(
            'UPDATE users SET reset_token = ?, reset_sent_at = ? WHERE email = ?',
            [hash('sha256', $token), date('Y-m-d H:i:s'), strtolower($email)]
        );
    }

    public static function findByResetToken(string $token): ?array
    {
        return Database::first('SELECT * FROM users WHERE reset_token = ?', [hash('sha256', $token)]);
    }

    public static function updatePassword(int $id, string $password): bool
    {
        return Database::execute(
            'UPDATE users SET password = ?, reset_token = NULL WHERE id = ?',
            [password_hash($password, PASSWORD_DEFAULT), $id]
        );
    }
}
