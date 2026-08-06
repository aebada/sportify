# Authentication

Sportify uses session-based login plus **custom Socialite-equivalent OAuth**. This is **not** Laravel Socialite (`composer require laravel/socialite` does not apply).

## Email / password

| Method | Path | Handler |
|--------|------|---------|
| GET | `/login` | `AuthController::showLogin` |
| POST | `/login` | `AuthController::login` |
| GET | `/register` | `AuthController::showRegister` |
| POST | `/register` | `AuthController::register` |
| POST | `/logout` | `AuthController::logout` |

Views: `views/auth/*` inside layout `views/layouts/auth.php`.

## Google OAuth (primary)

### Routes

| Method | Path | Handler (canonical routes) |
|--------|------|----------------------------|
| GET | `/auth/google` | `OAuthController::google` or `GoogleLoginController::start` |
| GET | `/auth/google/callback` | `OAuthController::googleCallback` / `GoogleLoginController::returned` |

On Hostinger, `.htaccess` may rewrite these to dedicated `public/oauth-google-*.php` entries so stale OPcache cannot serve an empty 500.

### Flow

1. User clicks **Continue with Google** on `/login` (or register with `?from=register&role=…`).
2. App builds authorize URL via `SocialAuthService::googleAuthUrl` / `GoogleOAuthStart::authUrl`:
   - `client_id`, `redirect_uri`, `scope=openid email profile`, CSRF `state`
3. Browser → Google consent → redirect to  
   `https://sportifyplus.de/auth/google/callback?code=…&state=…`
4. App exchanges `code` at Google token endpoint, fetches userinfo, finds/creates user (`users.google_id`), sets session, redirects home (or intended URL).
5. Callback **without** `code` must **302 → `/login?error=google`** (never blank 500).

### Redirect URI resolution

1. `GOOGLE_REDIRECT_URI` if set  
2. Else `{APP_URL}/auth/google/callback`  
3. Else production default `https://sportifyplus.de/auth/google/callback`

Implemented in `SocialAuthService::redirectUri('google')` and `config/services.php`.

### Env (placeholders only)

```env
GOOGLE_CLIENT_ID=YOUR_SPORTIFY_CLIENT_ID
GOOGLE_CLIENT_SECRET=YOUR_SPORTIFY_CLIENT_SECRET
GOOGLE_REDIRECT_URI=https://sportifyplus.de/auth/google/callback
APP_URL=https://sportifyplus.de
```

Config mirrors Laravel’s `config/services.php` shape but is plain PHP arrays — see `config/services.php` and `config/config.php` → `social.google.*`.

HOPn multi-app Console checklist: [google-oauth-hopn.md](google-oauth-hopn.md).

## Facebook & LinkedIn

Same pattern via `OAuthController` / `FacebookOAuthController` and `SocialAuthService`. Credentials: `FACEBOOK_*`, `LINKEDIN_*` in `.env`.

## Social button visibility (admin)

Admin UI: **`/admin/social-auth`** (`AdminSocialAuthController`).

| Provider | Visibility |
|----------|------------|
| Google | Always “visible” when used as primary (`SocialAuthSettings::isVisible('google')` → true) |
| Facebook | Toggle; default **off** |
| LinkedIn | Toggle; default **off** |

Persisted at `storage/cache/social_auth_visibility.json` (not secrets — only show/hide flags).

Login UI still checks `SocialAuthService::isConfigured($provider)` so missing env credentials hide/disable the flow even if a toggle is on.

Partial: `views/partials/social-auth-20260720-visibility.php` / Google button partials.

## DB

- `users.google_id` (unique) — Socialite-style provider id
- Avatar may live on `member_profiles.avatar` / social JSON (`google_avatar`)

Ops migrate URL (production): `/migrate-google-oauth-20260720.php` (run once after deploy when needed).

## Verify

```bash
curl -sI https://sportifyplus.de/auth/google | head -20
# Expect 302 → accounts.google.com

curl -sI https://sportifyplus.de/auth/google/callback | head -20
# Expect 302 → /login?error=google

curl -s https://sportifyplus.de/oauth-env-diag.php
# Expect redirect_uri=… and is_configured=yes
```

## Related

- [google-oauth-hopn.md](google-oauth-hopn.md)
- [architecture.md](architecture.md)
- [deployment-hostinger.md](deployment-hostinger.md)
