# RefereeX AI — Deploy & Operations Guide
# deploy-marker: refereex-deploy-doc-20260706-stack-align

## Summary

RefereeX AI landing page at **`/refereex-ai`** — standard Sportify PHP MVC (same stack as FIT-Pass, homepage).

| Layer | File | Purpose |
|-------|------|---------|
| Route | `config/routes-refereex-v1.php` | `GET /refereex-ai` → `RefereeXController@index` |
| Route (main) | `config/routes-20260621-memberships-v1.php` line ~206 | Same route before catch-alls |
| Controller | `app/Controllers/RefereeXController.php` | SEO meta, JSON-LD, `view()` via `layouts/app` |
| View | `views/pages/refereex-ai/index.php` | 17 sections using `app.css` classes |
| Main CSS | `public/assets/css/app.css` | Sportify design system (`.section`, `.card`, `.btn`, etc.) |
| Page CSS | `public/assets/css/refereex-ai.css` | Minimal scoped extras (pipeline, fusion slider, twin timeline) |
| Page JS | `public/assets/js/refereex-ai.js` | Fusion sliders + twin timeline only |
| i18n | `lang/en/refereex-messages.php` | Keys to merge into `lang/en/messages.php` |
| Nav | `views/partials/header-brand.php` | RefereeX AI in desktop + mobile nav |
| Homepage | `views/partials/home-refereex.php` | Promo card (patch home view to include) |

## Stack alignment (2026-07-06)

RefereeX now follows the **same Sportify stack** as other product pages:

- **Layout**: `views/layouts/app.php` via `Controller::view()` (header, footer, `app.css`, `app.js`)
- **No standalone theme**: removed `refereex-page` body class, custom dark SaaS palette, theme toggle
- **Classes**: `.hero`, `.section`, `.container`, `.card`, `.btn`, `.btn-primary`, `.stat-grid`, `.cta-banner`, `.table-wrap`, `table.data`, `.feature-list`, `.ai-rec`, etc.
- **i18n**: `__('refereex.*')` with keys in `lang/en/refereex-messages.php`
- **Links**: `route('contact')`, `route('refereex_ai')`
- **Reveal/count-up**: uses shared `app.js` (`data-reveal`, `data-count`) — no custom particle/canvas hero
| Root rewrite | `public/.htaccess-root-refereex-php` → `public_html/.htaccess` | `/refereex-ai` → `live-sports?refereex=1` |
| Site prepend | `public/site-prepend-20260706-refereex.php` | Early handler before OPcache (bypass) |
| Bootstrap | `bootstrap-20260621-authfix.php` | Fallback early handler for `?refereex=1` |
| Standalone | `public/refereex-ai.php` | Direct PHP entry |
| Front controller | `public/index-20260704-refereex-v1.php` | Early `/refereex-ai` block |
| 404 fallback | `views/errors/404.php` | Serves RefereeX if path matches |
| OPcache reset | `public/opcache-reset-refereex.php` | Manual cache purge script |

## Deploy

```bash
bash scripts/deploy_refereex_20260706.sh
```

### Post-deploy: patch homepage promo

```bash
curl 'https://sportifyplus.de/patch-home-refereex-20260706.php?key=refereex-home-20260706'
```

## Cache purge (REQUIRED on Hostinger)

FTP uploads often do not appear until cache is purged:

1. **hPanel** → Website → **Cache Manager** → Purge All
2. Or **LiteSpeed Cache** → Purge → Purge All
3. Visit: `https://sportifyplus.de/opcache-reset-refereex.php`
4. Wait 2–5 minutes, then verify

### Verify commands

```bash
curl -sSI https://sportifyplus.de/refereex-ai | grep -E 'HTTP|x-sportify'
curl -sS https://sportifyplus.de/refereex-ai | grep -E 'rx-hero|rx-problem|RefereeX AI'
curl -sSI https://sportifyplus.de/ | head -3          # no regression
curl -sSI https://sportifyplus.de/fit-pass | head -3  # no regression
curl -sSI https://sportifyplus.de/live-sports | head -3
```

### Latest deploy test results (2026-07-06)

| URL | Status | Notes |
|-----|--------|-------|
| `/refereex-ai` | **404** | Stale CDN/OPcache — awaiting hPanel purge |
| `/refereex-ai.php` | **404** | Same |
| `/opcache-reset-refereex.php` | **404** | New file not yet in CDN cache |
| `/opcache-purge.php?serve=refereex` | **200** | Returns old probe text (stale OPcache) |
| `/` | **200** | OK — no regression |
| `/fit-pass` | **200** | OK — no regression |
| `/live-sports` | **200** | OK — no regression |

After hPanel cache purge, expect:
- `X-Sportify-RefereeX` header on `/refereex-ai`
- Body contains `rx-hero`, `rx-problem`, `RefereeX AI`
- Temporary bridge: `/opcache-purge.php?serve=refereex` serves full page

## Files changed (2026-07-06 stack alignment)

- `views/pages/refereex-ai/index.php` — 17 sections using Sportify `app.css` patterns
- `app/Controllers/RefereeXController.php` — removed custom `bodyClass`; minimal `extraStyles`/`extraScripts`
- `public/assets/css/refereex-ai.css` — ~80 lines, Sportify CSS variables only
- `public/assets/js/refereex-ai.js` — fusion sliders + twin timeline (no theme toggle/particles)
- `lang/en/refereex-messages.php` — i18n keys (merge into `lang/en/messages.php` on server)
- `views/partials/header-brand.php` — nav links
- `views/partials/home-refereex.php` — homepage promo
- `bootstrap-20260621-authfix.php` — `?refereex=1` early handler
- `public/site-prepend-20260706-refereex.php` — site-wide early handler
- `public/.htaccess-root-refereex-php` — root routing
- `public/.htaccess` — public routing to `refereex-ai.php` + front controller
- `public/.user.ini-refereex-account-root` — auto_prepend at account-root path

## Updating later

1. Edit view/CSS/JS locally
2. Run `bash scripts/deploy_refereex_20260706.sh`
3. Purge hPanel cache
4. Verify with curl

## Contact form

Pilot/demo CTAs link to `route('contact')` with `?subject=RefereeX%20Pilot` (no DB migration).

## Known blocker

Hostinger OPcache + hCDN can freeze PHP files. Multiple bypass layers deployed:

1. `site-prepend` (new filename each deploy)
2. Bootstrap early handler
3. `TvScheduleController` / `LiveHd7SyncService` bridge
4. Router 404 fallback
5. Root `.htaccess` internal rewrite to `?refereex=1`

If `/refereex-ai` still 404 after deploy → **hPanel cache purge required**.

## FTP

- Host: `92.113.19.130`
- User: `u234903558.sportifywebsite`
- Docroot: `public_html/` (account root, not `domains/...` only)
