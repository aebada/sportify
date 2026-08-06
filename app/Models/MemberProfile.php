<?php

namespace App\Models;

use App\Core\App;
use App\Core\Database;
use App\Support\Media;
use App\Support\Uploader;

class MemberProfile
{
    public const SOCIAL_KEYS = ['instagram', 'twitter', 'tiktok', 'youtube', 'linkedin'];

    public static function findByUserId(int $userId): ?array
    {
        if (!Database::available()) {
            return null;
        }
        $row = Database::first('SELECT * FROM member_profiles WHERE user_id = ?', [$userId]);
        return $row ? self::decorate($row) : null;
    }

    public static function findByUsername(string $username): ?array
    {
        if (!Database::available()) {
            return null;
        }
        $row = Database::first('SELECT * FROM member_profiles WHERE username = ?', [strtolower($username)]);
        return $row ? self::decorate($row) : null;
    }

    /** Create profile on registration if missing. */
    public static function ensureForUser(array $user): array
    {
        $existing = self::findByUserId((int) $user['id']);
        if ($existing) {
            return $existing;
        }

        $username = self::uniqueUsername(self::slugify($user['name']), (int) $user['id']);
        $now = date('Y-m-d H:i:s');
        Database::execute(
            'INSERT INTO member_profiles (user_id, username, social, created_at, updated_at) VALUES (?, ?, ?, ?, ?)',
            [(int) $user['id'], $username, json_encode([]), $now, $now]
        );
        return self::findByUserId((int) $user['id']) ?? [
            'user_id' => (int) $user['id'],
            'username' => $username,
            'social' => [],
        ];
    }

    public static function update(int $userId, array $data): bool
    {
        $social = [];
        foreach (self::SOCIAL_KEYS as $key) {
            $url = trim((string) ($data['social'][$key] ?? ''));
            if ($url !== '') {
                $social[$key] = $url;
            }
        }

        $username = self::slugify((string) ($data['username'] ?? ''));
        if ($username === '') {
            return false;
        }

        $taken = Database::first(
            'SELECT id FROM member_profiles WHERE username = ? AND user_id != ?',
            [$username, $userId]
        );
        if ($taken) {
            return false;
        }

        return Database::execute(
            'UPDATE member_profiles SET username = ?, headline = ?, bio = ?, location = ?, website = ?, social = ?, updated_at = ? WHERE user_id = ?',
            [
                $username,
                trim((string) ($data['headline'] ?? '')),
                trim((string) ($data['bio'] ?? '')),
                trim((string) ($data['location'] ?? '')),
                trim((string) ($data['website'] ?? '')),
                json_encode($social),
                date('Y-m-d H:i:s'),
                $userId,
            ]
        );
    }

    public static function setAvatar(int $userId, string $path): bool
    {
        return Database::execute(
            'UPDATE member_profiles SET avatar = ?, updated_at = ? WHERE user_id = ?',
            [$path, date('Y-m-d H:i:s'), $userId]
        );
    }

    /** Lookup linked Google account (users.google_id, then social JSON fallback). */
    public static function findUserIdByGoogleId(string $googleId): ?int
    {
        if (!Database::available() || $googleId === '') {
            return null;
        }

        $byColumn = User::findByGoogleId($googleId);
        if ($byColumn !== null) {
            return (int) $byColumn['id'];
        }

        $driver = (string) App::config('db.driver', 'sqlite');
        if ($driver === 'mysql') {
            $row = Database::first(
                'SELECT user_id FROM member_profiles WHERE JSON_UNQUOTE(JSON_EXTRACT(social, \'$.google_id\')) = ? LIMIT 1',
                [$googleId]
            );
        } else {
            $row = Database::first(
                'SELECT user_id FROM member_profiles WHERE json_extract(social, \'$.google_id\') = ? LIMIT 1',
                [$googleId]
            );
        }

        return $row ? (int) $row['user_id'] : null;
    }

    /** Persist Socialite-equivalent Google profile (google_id + avatar URL). */
    public static function syncGoogleOAuth(int $userId, string $googleId, ?string $name, ?string $avatarUrl): void
    {
        if (!Database::available()) {
            return;
        }

        $user = User::find($userId);
        $resolvedName = ($name !== null && trim($name) !== '') ? trim($name) : null;
        if ($resolvedName !== null) {
            User::updateName($userId, $resolvedName);
        }

        User::setGoogleId($userId, $googleId);

        self::ensureForUser([
            'id'   => $userId,
            'name' => $resolvedName ?? ($user['name'] ?? 'Member'),
        ]);
        $row = Database::first('SELECT social, avatar FROM member_profiles WHERE user_id = ?', [$userId]);
        $social = [];
        if (!empty($row['social'])) {
            $decoded = json_decode((string) $row['social'], true);
            if (is_array($decoded)) {
                $social = $decoded;
            }
        }
        $social['google_id'] = $googleId;
        if ($avatarUrl !== null && $avatarUrl !== '') {
            $social['google_avatar'] = $avatarUrl;
        } elseif (!empty($social['google_avatar'])) {
            // Keep existing avatar when Google omits picture (AI Pass COALESCE behavior).
        }

        Database::execute(
            'UPDATE member_profiles SET social = ?, updated_at = ? WHERE user_id = ?',
            [json_encode($social), date('Y-m-d H:i:s'), $userId]
        );
    }

    public static function withUser(array $profile): array
    {
        $user = User::find((int) $profile['user_id']);
        $profile['user'] = $user;
        $profile['name'] = $user['name'] ?? 'Member';
        $profile['role'] = $user['role'] ?? 'fan';
        return $profile;
    }

    protected static function decorate(array $row): array
    {
        $row['social'] = !empty($row['social']) ? json_decode($row['social'], true) : [];
        if (!is_array($row['social'])) {
            $row['social'] = [];
        }
        $row['avatar_url'] = Uploader::url($row['avatar'] ?? null);
        if (!$row['avatar_url'] && !empty($row['social']['google_avatar'])) {
            $row['avatar_url'] = (string) $row['social']['google_avatar'];
        }
        if (!$row['avatar_url']) {
            $female = false;
            $row['avatar_url'] = Media::playerPhoto('member-' . $row['username'], 0, $female);
        }
        return $row;
    }

    public static function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        return trim($s, '-') ?: 'member';
    }

    protected static function uniqueUsername(string $base, int $userId): string
    {
        $base = substr($base, 0, 50) ?: 'member';
        $candidate = $base;
        $i = 0;
        while (Database::first('SELECT id FROM member_profiles WHERE username = ?', [$candidate])) {
            $i++;
            $candidate = $base . '-' . $userId . ($i > 1 ? '-' . $i : '');
        }
        return $candidate;
    }

    public static function roleLabel(string $role): string
    {
        return \App\Support\Rbac::roleLabel($role);
    }
}
