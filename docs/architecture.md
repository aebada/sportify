# Architecture — Sportify Plus

Sportify is a **custom PHP MVC** application. It is **not** Laravel, Symfony, or WordPress. OAuth uses Socialite-*shaped* config and URLs only; there is no `laravel/socialite` package and no Composer requirement for Socialite.

## Request lifecycle

```
Browser
  → public/.htaccess (rewrites; dedicated OAuth PHP entries for /auth/google*)
  → public/index.php (versioned front-controller delegate)
  → bootstrap.php
  → App\Core\App::boot($config) + App::run()
  → config/routes*.php registers routes on App::$router
  → Router::dispatch(Request)
  → Controller action
  → View::render → views/… + layouts/…
```

### Document roots (production)

Live Hostinger path (typical):

`/home/<account>/domains/sportifyplus.de/public_html`

The repo layout expects:

| Path | Role |
|------|------|
| `public/` | Web root (assets, front controller, probes) |
| `bootstrap.php` | Shared boot (web + CLI) |
| `app/` | Controllers, Core, Models, Services, Support |
| `config/` | `config.php`, `services.php`, route files |
| `views/` | PHP templates |
| `lang/` | Translations |
| `database/` | Migrations / seeds |
| `storage/` | SQLite, cache JSON, logs (not committed) |
| `scripts/` | FTP deploy / ops helpers |

On Hostinger, the tree under `public_html` often mirrors the repo (or `public/` contents are promoted to docroot). Deploy scripts upload to multiple FTP roots — see [deployment-hostinger.md](deployment-hostinger.md).

## Bootstrap

[`bootstrap.php`](../bootstrap.php):

- Defines start time, host-specific OPcache invalidation (OnlyTalents hosts)
- Sets no-store cache headers
- **Never** sends `X-LiteSpeed-Purge` on normal requests (empty 500 risk on Hostinger)
- Loads helpers, autoloads `App\` classes, builds config from `config/config.php`
- Starts `App::boot()` then `App::run()`

Light-bootstrap paths skip heavy work for diag/purge endpoints (`deploy-check`, `opcache-purge`, `oauth-env-diag`, OnlyTalents setup probes, etc.).

## App core (`app/Core`)

| Class | Role |
|-------|------|
| `App` | Static container: `$config`, `$router`, `$request`; loads routes; `dispatch` |
| `Router` | Method + path → `[Controller, action]` |
| `Request` | Input / URI helpers |
| `Controller` | `view()`, `redirect()`, auth/permission helpers |
| `View` | Renders `views/{dot.path}.php` inside a layout |
| `Auth` / `Database` / `Csrf` / `Lang` | Session user, PDO, tokens, i18n |

`App::run()` chooses route files:

- **OnlyTalents host** (`OT_HOST`, e.g. `talents.sportifyplus.de`) → `config/routes-onlytalents.php`
- **Main site** → `config/routes.php`, fallback `config/routes-20260621-memberships-v1.php`

## Controllers (`app/Controllers`)

Thin HTTP adapters. Examples:

- `AuthController` — `/login`, `/register`, logout
- `OAuthController` / `GoogleLoginController` — Google (and other) OAuth
- `OnlyTalentsDiscoveryController` / `OnlyTalentsController` — shorts & OT pages
- `RefereeXController` — `/refereex-ai`
- `AdminSocialAuthController` — `/admin/social-auth`
- Feature controllers: players, scout, marketplace, FIT-Pass, transfer bureau, etc.

## Services (`app/Services`)

Business logic outside controllers:

- `SocialAuthService` — OAuth authorize URLs, token exchange, profile fetch
- `SocialAuthSettings` — admin visibility JSON
- `GoogleOAuthStart` / `GoogleAuthService` — Google-specific start/callback helpers
- `OtLandingShortsService` / `TalentShortsService` — OnlyTalents feed
- AI / Manus / wellness / translation services as needed

## Models (`app/Models`)

Repositories and entities over PDO (`User`, `MemberProfile`, player/news/shorts repos, etc.). Schema bootstrap helpers live in `DatabaseSetup`.

## Views (`views/`)

| Area | Path |
|------|------|
| Layouts | `views/layouts/{app,auth,admin,onlytalents}.php` |
| Pages | `views/pages/…` |
| Auth | `views/auth/…` |
| Partials | `views/partials/…` |
| Admin | `views/admin/…` |

Controllers call `$this->view('auth.login', $data, 'auth')` → layout `auth` + page `auth/login`.

## Config

- [`config/config.php`](../config/config.php) — app, db, paths, social, OnlyTalents, Manus, etc.
- [`config/services.php`](../config/services.php) — Socialite-shaped `google.client_id/secret/redirect`
- Route modules: `routes.php`, `routes-onlytalents.php`, `routes-refereex-v1.php`, dated memberships/wellness variants

`.env` is loaded inline (no Composer `vlucas/phpdotenv`).

## Front controller & OAuth rewrites

[`public/.htaccess`](../public/.htaccess) maps Google OAuth to dedicated entry scripts (fresh filenames to bypass stale OPcache):

- `/auth/google` → `oauth-google-start-*.php`
- `/auth/google/callback` → `oauth-google-cb-*.php`

Other paths fall through to `index.php`.

## Related docs

- [auth.md](auth.md) — OAuth & login flow
- [only-talents.md](only-talents.md) — OT routing & feed
- [deployment-hostinger.md](deployment-hostinger.md) — production caveats
- [google-oauth-hopn.md](google-oauth-hopn.md) — HOPn Google Cloud
