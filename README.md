# Sportify Plus

Football talent platform at **[sportifyplus.de](https://sportifyplus.de)** — player discovery, scouting, transfers, FIT-Pass memberships, **OnlyTalents** short-form video, and **RefereeX AI**.

Repository: [github.com/aebada/sportify](https://github.com/aebada/sportify)

## Stack

| Layer | Technology |
|-------|------------|
| App | **Custom PHP MVC** (`App\Core\App`, controllers, views) — **not Laravel** |
| Auth OAuth | **Socialite-equivalent** custom OAuth (`SocialAuthService` / `OAuthController`) — **not** `laravel/socialite`; Composer Socialite is N/A |
| Front controller | `public/index.php` → `bootstrap.php` → `App::run()` |
| Config | `config/config.php` + lightweight `.env` loader (no Composer dotenv) |
| DB | MySQL (production) or SQLite (local default) |
| Hosting | Hostinger shared hosting (LiteSpeed / OPcache) |
| i18n | `lang/{en,de,ar}` |

There is **no** Laravel framework and **no** Composer requirement for day-to-day app runtime. Deploy scripts may use `lftp` / `curl` from the host machine.

## Requirements

- **PHP 8.x** (developed against 8.x; avoid EOL 7.x)
- **MySQL** (production) or **SQLite** (local)
- Apache/`mod_rewrite` or LiteSpeed rewrite (see `public/.htaccess`)
- For local: PHP built-in server is enough
- For deploy scripts: `lftp` or `curl`, FTP credentials via env (never commit secrets)

## Local setup

```bash
cd /path/to/sportify-web   # this repo root

cp .env.example .env
# Edit .env — APP_URL, optional GOOGLE_*, DB_*

# Default: SQLite (DB_CONNECTION=sqlite). Empty DB_DATABASE → storage/database.sqlite
mkdir -p storage/cache storage/logs
# Ensure storage is writable

php -S localhost:8080 -t public
```

Open `http://localhost:8080`. Login: `/login`.

Production MySQL example in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sportify
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

## Environment variables

Copy from [`.env.example`](.env.example). **Never commit** `.env` or real secrets.

| Variable | Purpose |
|----------|---------|
| `APP_NAME` / `APP_ENV` / `APP_DEBUG` / `APP_URL` / `APP_KEY` | App identity & base URL |
| `DB_CONNECTION` / `DB_*` | sqlite or mysql |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI` | Google OAuth (HOPn app #4) |
| `FACEBOOK_*` / `LINKEDIN_*` | Optional social providers |
| `OT_HOST` / `OT_URL` / `OT_MAIN_URL` / `SESSION_DOMAIN` | OnlyTalents subdomain |
| `MANUS_API_KEY` / `OPENAI_API_KEY` | Optional AI integrations |

Placeholders only in repo examples (`YOUR_SPORTIFY_CLIENT_ID`, empty secrets).

## Key URLs

| Path | Description |
|------|-------------|
| `/` | Homepage |
| `/login` | Email/password + social login |
| `/auth/google` | Start Google OAuth |
| `/auth/google/callback` | OAuth return (exchange code → session) |
| `/only-talents` | OnlyTalents TikTok-style shorts feed |

### Potential Partners CRM

```bash
php sportify migrate
php sportify seed-partners
php sportify invite-partners --dry-run   # then --limit=100 to queue; --send when MAIL_* set
```

Admin: `/admin/partners` — see [docs/PARTNERS-CRM.md](docs/PARTNERS-CRM.md).
| `/only-talents/discover` | Discovery browse |
| `/refereex-ai` | RefereeX AI product page |
| `/admin/social-auth` | Admin toggles for Facebook/LinkedIn button visibility |
| `/fit-pass` | FIT-Pass memberships |
| `/oauth-env-diag.php` | Production OAuth env probe (ops) |

Subdomain `talents.sportifyplus.de` may redirect into main-domain `/only-talents/*` paths depending on gateway deploy.

## Product areas

- **Talent / players** — profiles, search, scout tools, marketplace, transfer bureau
- **OnlyTalents** — vertical shorts feed, categories, communities, upload
- **RefereeX AI** — referee AI landing (`/refereex-ai`); see also `REFEREEX-DEPLOY.md`
- **FIT-Pass / memberships** — plans and wellness-related routes
- **Live sports / news / transfers** — content hubs

## Deployment (Hostinger)

Production docroot is typically:

`/home/<user>/domains/sportifyplus.de/public_html`

FTP often exposes **three roots** that can diverge:

1. `/` (account root)
2. `/public_html`
3. `/domains/sportifyplus.de/public_html` ← usually the live PHP disk

When FTP uploads do not match what PHP serves, use **hPanel → File Manager** on the domain path. After upload: purge **OPcache** / LiteSpeed carefully — do **not** send `X-LiteSpeed-Purge` on normal web responses (can cause empty HTTP 500).

Details: [docs/deployment-hostinger.md](docs/deployment-hostinger.md)

Example OAuth deploy (password via env):

```bash
FTP_PASS='…' ./scripts/deploy_google_oauth_sportify_hopn.sh
```

## Google OAuth / HOPn ecosystem

Sportify is app **#4** of five HOPn Identity Google OAuth apps (including **Praktix**). Same redirect shape as Socialite (`/auth/google/callback`), but Sportify’s implementation is custom PHP.

Full Console checklist, origins, and redirect URIs: **[docs/google-oauth-hopn.md](docs/google-oauth-hopn.md)**

## OnlyTalents shorts feed

Main entry: [`/only-talents`](https://sportifyplus.de/only-talents) — immersive vertical shorts (`tiktokLanding`), filters, load-more API, likes. See [docs/only-talents.md](docs/only-talents.md).

## Admin social login toggles

`/admin/social-auth` (requires admin permission):

- **Google** — always treated as visible when configured (primary provider)
- **Facebook / LinkedIn** — show/hide buttons via `SocialAuthSettings` (`storage/cache/social_auth_visibility.json`)

Credentials still come from `.env`; the admin page only controls UI visibility.

## Documentation

| Doc | Topic |
|-----|--------|
| [docs/architecture.md](docs/architecture.md) | Bootstrap, App, router, controllers, views |
| [docs/auth.md](docs/auth.md) | Login, Google OAuth flow, social visibility |
| [docs/google-oauth-hopn.md](docs/google-oauth-hopn.md) | HOPn 5-app Google Cloud setup |
| [docs/deployment-hostinger.md](docs/deployment-hostinger.md) | FTP roots, OPcache, LiteSpeed |
| [docs/only-talents.md](docs/only-talents.md) | Shorts feed & routes |
| [REFEREEX-DEPLOY.md](REFEREEX-DEPLOY.md) | RefereeX deploy notes |

## License / contact

Private project — Sportify Plus / HOPn. Production: https://sportifyplus.de
