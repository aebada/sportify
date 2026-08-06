# Google OAuth — HOPn Identity (Socialite-compatible redirects)

> **Sportify Plus architecture:** Not Laravel. Custom PHP MVC (`App\Controllers`, `config/config.php`).  
> Google Sign-In is a **Socialite-equivalent custom OAuth** implementation (`SocialAuthService` + `OAuthController`), not `laravel/socialite`.  
> Other HOPn apps may be Laravel + Socialite; they share the same redirect URI shape (`/auth/google/callback`).

Shared Google Cloud project for HOPn Identity. Each production site uses Socialite-style `/auth/google/callback`.

**Recommendation:** Eventually centralize on `auth.ehopn.com` (one OAuth client, one redirect, apps trust the identity host). Until then, keep per-app Web clients (or one client with all URIs below) so `redirect_uri_mismatch` stays rare.

## Ecosystem apps

| # | App | Site | OAuth callback | Stack |
|---|-----|------|----------------|-------|
| 1 | MunichTech EXPO | https://munichtechexpo.com | https://munichtechexpo.com/auth/google/callback | Laravel Socialite |
| 2 | Invoice eHOPn | https://invoice.ehopn.com | https://invoice.ehopn.com/auth/google/callback | Laravel Socialite |
| 3 | eHOPn Models | https://models.ehopn.com | https://models.ehopn.com/auth/google/callback | Laravel Socialite |
| 4 | **Sportify Plus** | https://sportifyplus.de | https://sportifyplus.de/auth/google/callback | **Custom PHP (Socialite-equivalent)** |
| 5 | **Praktix** | https://praktix.online | https://praktix.online/auth/google/callback | Laravel Socialite |

Each app should have its **own OAuth 2.0 Web client** in the same Google Cloud project (recommended; avoids `redirect_uri_mismatch` when URIs differ). Alternatively, one shared Web client may list **all** origins and redirect URIs below.

## Authorized JavaScript origins (all 5)

Add exactly these origins (plus `www` variants only if that hostname actually serves the app):

- `https://munichtechexpo.com`
- `https://invoice.ehopn.com`
- `https://models.ehopn.com`
- `https://sportifyplus.de`
- `https://praktix.online`

## Authorized redirect URIs (all 5)

- `https://munichtechexpo.com/auth/google/callback`
- `https://invoice.ehopn.com/auth/google/callback`
- `https://models.ehopn.com/auth/google/callback`
- `https://sportifyplus.de/auth/google/callback`
- `https://praktix.online/auth/google/callback`

## Google Cloud Console checklist

1. Open [Google Cloud Console → APIs & Services → Credentials](https://console.cloud.google.com/apis/credentials).
2. Select the **HOPn / Identity** project.
3. Either **edit** the shared Web client, or **Create credentials → OAuth client ID → Web application** per app.
4. For Sportify, name e.g. `Sportify Plus (sportifyplus.de)`.
5. Set **Authorized JavaScript origins** and **Authorized redirect URIs** to the full lists above (or the subset for that client).
6. Save and copy **Client ID** / **Client secret** into that app’s production `.env` only.

### Sportify Plus (#4) — client naming example

- Name: `Sportify Plus (sportifyplus.de)`
- Origins: `https://sportifyplus.de` (+ www if used)
- Redirect: `https://sportifyplus.de/auth/google/callback`
- Composer / Socialite: **N/A** (custom OAuth)

### Praktix (#5) — client naming example

- Name: `Praktix (praktix.online)`
- Origins: `https://praktix.online` (+ www if used)
- Redirect: `https://praktix.online/auth/google/callback`
- Codebase: `/Volumes/All/Dev/Praktix` (Laravel Socialite; Hostinger FTP layout — see that repo’s `DEPLOY.md`)

## Sportify production `.env`

On Hostinger (`domains/sportifyplus.de/public_html/.env` and mirrors on `.` / `public_html`):

```env
GOOGLE_CLIENT_ID=YOUR_SPORTIFY_CLIENT_ID
GOOGLE_CLIENT_SECRET=YOUR_SPORTIFY_CLIENT_SECRET
GOOGLE_REDIRECT_URI=https://sportifyplus.de/auth/google/callback
APP_URL=https://sportifyplus.de
```

Config mapping (Socialite-equivalent):

- `config/services.php` → `google.client_id` / `client_secret` / `redirect`
- `config/config.php` → `social.google.*` (same env keys; used by `SocialAuthService`)

Or patch via:

```bash
php scripts/patch_production_google_env.php \
  --pass='FTP_PASSWORD' \
  --client-id='YOUR_SPORTIFY_CLIENT_ID' \
  --client-secret='YOUR_SPORTIFY_CLIENT_SECRET' \
  --redirect-uri='https://sportifyplus.de/auth/google/callback'
```

## Praktix production `.env`

On Hostinger (`public_html/back/laravel/.env` via FTP; local mirror `.env.production`):

```env
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://praktix.online/auth/google/callback
```

Upload with Praktix `python3 scripts/upload-server-env.py` after editing `.env.production`. Never commit real secrets.

## Sportify routes & flow

| Method | Path | Handler |
|--------|------|---------|
| GET | `/auth/google` | Start → Google authorize URL |
| GET | `/auth/google/callback` | Exchange code → find/create user → login → home |

Hostinger serves these via dedicated PHP entries (`oauth-google-start-*.php` / `oauth-google-callback-*.php`) to avoid empty HTTP 500 from stale OPcache / LiteSpeed purge on the front controller.

Callback without `code` must **302 → `/login?error=google`** (never blank 500).

## DB columns

- `users.google_id` (VARCHAR, unique) — Socialite-style
- `member_profiles.avatar` — already in schema; migrate script ensures it exists
- Avatar URL from Google also stored in `member_profiles.social.google_avatar`

Run after deploy: `https://sportifyplus.de/migrate-google-oauth-20260720.php`

## Verify

**Sportify** (after deploy and `.env` update):

```bash
curl -sI https://sportifyplus.de/auth/google | head -20
# Expect: 302 Location: https://accounts.google.com/...

curl -sI https://sportifyplus.de/auth/google/callback | head -20
# Expect: 302 Location: /login?error=google

curl -s https://sportifyplus.de/oauth-env-diag.php
```

Expect `redirect_uri=https://sportifyplus.de/auth/google/callback` and `is_configured=yes`.

**Praktix:** open https://praktix.online/login → Google → confirm callback lands on `/auth/google/callback` without `redirect_uri_mismatch`.

## Deploy code (Sportify) — all three FTP roots

```bash
FTP_PASS='…' ./scripts/deploy_google_oauth_sportify_hopn.sh
```

Uploads to:

1. `/` (account root)
2. `/public_html`
3. `/domains/sportifyplus.de/public_html`

Then purge OPcache and hit the migrate URL once.

## Related docs

- [auth.md](auth.md) — Sportify login + OAuth flow
- [deployment-hostinger.md](deployment-hostinger.md) — FTP roots / OPcache / LiteSpeed
- [architecture.md](architecture.md) — custom PHP MVC overview
- [../README.md](../README.md) — project entry point
