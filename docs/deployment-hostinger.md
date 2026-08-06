# Deployment — Hostinger (Sportify Plus)

Production site: **https://sportifyplus.de**

Shared hosting with LiteSpeed + OPcache. FTP layout and PHP document root are easy to confuse — treat the domain path as source of truth.

## Three FTP roots (important)

The same Hostinger FTP account often exposes:

| FTP path | Notes |
|----------|--------|
| `/` (`.`) | Account home — may hold a partial mirror |
| `/public_html` | Generic public_html — **may not** be the live domain disk |
| `/domains/sportifyplus.de/public_html` | **Usually the live PHP docroot** |

Deploy scripts that matter (e.g. `scripts/deploy_google_oauth_sportify_hopn.sh`) upload to **all three** so mirrors stay aligned.

### When FTP ≠ what PHP serves

If uploads “succeed” but the live site still shows old HTML/PHP:

1. Confirm docroot via a live probe such as `/deploy-check` (path printed by the app).
2. Use **hPanel → File Manager** and navigate to  
   `domains/sportifyplus.de/public_html`  
   (not only the FTP client’s default folder).
3. Re-upload there, then purge OPcache (below).

Typical absolute path:

`/home/<FTP_USER_HOME>/domains/sportifyplus.de/public_html`

Use placeholders for credentials:

```bash
FTP_HOST=YOUR_FTP_HOST
FTP_USER=YOUR_FTP_USER
FTP_PASS='YOUR_FTP_PASSWORD'
```

**Never commit** real FTP passwords or `.env` secrets. Prefer env vars over hardcoding in scripts.

## What to upload

Minimum mental model:

- App code: `app/`, `bootstrap.php`, `config/`, `views/`, `lang/`, `database/`
- Web: contents of `public/` (front controller, assets, `.htaccess`, OAuth entry PHP files)
- Production `.env` on the server only (create/edit in File Manager or patch script)

Do not upload local mirrors: `.ftp-*`, `.deploy-*`, `.oauth-fetch*`, `.env`.

## OPcache

PHP may keep old bytecode after FTP overwrite.

1. Hit an ops purge endpoint if deployed (e.g. `/opcache-purge.php`, feature-specific `opcache-reset-*.php`).
2. Or hPanel → **Cache Manager** / LiteSpeed → purge.
3. Wait 1–5 minutes and re-verify with `curl -sI`.

Dedicated OAuth entry filenames (`oauth-google-start-*.php`, `oauth-google-cb-*.php`) exist partly to **bypass** sticky OPcache on the main front controller.

## LiteSpeed purge caution

**Do not** emit `X-LiteSpeed-Purge` on ordinary page responses (home, login, OAuth redirects). On Hostinger this has caused **empty HTTP 500** responses. Bootstrap intentionally avoids that header on web requests; keep it that way.

Prefer:

- Explicit purge URLs / hPanel purge
- `opcache_invalidate` / `opcache_reset` in dedicated admin/ops scripts

## Google OAuth deploy example

```bash
export FTP_PASS='YOUR_FTP_PASSWORD'
./scripts/deploy_google_oauth_sportify_hopn.sh
```

Then:

1. Ensure production `.env` has `GOOGLE_*` and `APP_URL` (see [google-oauth-hopn.md](google-oauth-hopn.md)).
2. Run migrate URL once if columns are missing.
3. Verify `/auth/google` and `/oauth-env-diag.php`.

Optional env patch helper: `scripts/patch_production_google_env.php` (pass credentials via CLI flags — do not commit real values).

## OnlyTalents / subdomain

`talents.sportifyplus.de` may use a gateway under `…/public_html/talents` that redirects to  
`https://sportifyplus.de/only-talents…`.  
Main feed logic lives on the **main domain** routes. See [only-talents.md](only-talents.md).

## RefereeX

See repo root [`REFEREEX-DEPLOY.md`](../REFEREEX-DEPLOY.md) for `/refereex-ai` deploy scripts and cache notes.

## Post-deploy checklist

```bash
curl -sI https://sportifyplus.de/ | head -5
curl -sI https://sportifyplus.de/login | head -5
curl -sI https://sportifyplus.de/auth/google | head -10
curl -sI https://sportifyplus.de/only-talents | head -5
curl -sI https://sportifyplus.de/refereex-ai | head -5
```

Confirm `x-sportify-*` diagnostic headers when present, and that OAuth redirects to Google (not 500).
