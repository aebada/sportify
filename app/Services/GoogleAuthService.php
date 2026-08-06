<?php

namespace App\Services;

use App\Models\MemberProfile;
use App\Models\User;
use App\Support\Rbac;

/**
 * Google sign-in user resolution — Socialite-equivalent find-or-create.
 * Order: users.google_id → email link → create.
 * deploy-marker: google-auth-service-20260720f
 */
final class GoogleAuthService
{
    /**
     * @param array{google_id?:string,provider_id?:string,email:string,name?:?string,avatar_url?:?string} $profile
     * @return array{user: array, is_new: bool}
     */
    public static function findOrCreateUser(array $profile, string $role = 'player'): array
    {
        $googleId = trim((string) ($profile['google_id'] ?? $profile['provider_id'] ?? ''));
        $email = strtolower(trim((string) ($profile['email'] ?? '')));
        $name = isset($profile['name']) ? trim((string) $profile['name']) : null;
        $name = $name !== '' ? $name : null;
        $avatarUrl = isset($profile['avatar_url']) ? trim((string) $profile['avatar_url']) : null;
        $avatarUrl = $avatarUrl !== '' ? $avatarUrl : null;

        if ($email === '' || $googleId === '') {
            throw new \RuntimeException('Google did not return an email address. Use an account with email permission, or register with email.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Google returned an invalid email address. Please try again or register with email.');
        }

        $byGoogle = User::findByGoogleId($googleId);
        if ($byGoogle !== null) {
            $userId = (int) $byGoogle['id'];
            MemberProfile::syncGoogleOAuth($userId, $googleId, $name, $avatarUrl);

            return ['user' => User::find($userId) ?? $byGoogle, 'is_new' => false];
        }

        $existingId = MemberProfile::findUserIdByGoogleId($googleId);
        if ($existingId !== null) {
            $user = User::find($existingId);
            if ($user !== null) {
                MemberProfile::syncGoogleOAuth($existingId, $googleId, $name, $avatarUrl);

                return ['user' => User::find($existingId) ?? $user, 'is_new' => false];
            }
        }

        $byEmail = User::findByEmail($email);
        if ($byEmail !== null) {
            $userId = (int) $byEmail['id'];
            MemberProfile::syncGoogleOAuth($userId, $googleId, $name, $avatarUrl);

            return ['user' => User::find($userId) ?? $byEmail, 'is_new' => false];
        }

        $displayName = $name ?? 'Sportify User';
        $role = Rbac::normalizeRole($role);
        $id = User::createSocial([
            'name'  => $displayName,
            'email' => $email,
            'role'  => $role,
        ]);

        if (!$id) {
            throw new \RuntimeException('Could not create your account. Please try email registration.');
        }

        MemberProfile::syncGoogleOAuth($id, $googleId, $name, $avatarUrl);

        $user = User::find($id);
        if ($user === null) {
            throw new \RuntimeException('Could not create your account. Please try email registration.');
        }

        return ['user' => $user, 'is_new' => true];
    }
}
