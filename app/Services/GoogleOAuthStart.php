<?php

namespace App\Services;

/**
 * Fresh Google OAuth start + callback (OPcache-safe deploy marker).
 * Thin Socialite-equivalent wrapper over SocialAuthService — no hardcoded secrets.
 * deploy-marker: google-oauth-start-20260720f
 */
final class GoogleOAuthStart
{
    public static function authUrl(string $from = 'login', string $role = 'player'): string
    {
        return SocialAuthService::googleAuthUrl($from, $role);
    }

    /** @return array{google_id:string,name:?string,email:string,provider:string,provider_id:string,avatar_url:?string} */
    public static function handleCallback(string $code, ?string $state): array
    {
        return SocialAuthService::handleGoogleCallback($code, $state);
    }
}
