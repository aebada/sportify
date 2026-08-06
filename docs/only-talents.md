# OnlyTalents

TikTok-style **shorts** product inside Sportify Plus: vertical video feed, discovery, categories, profiles, communities, and upload.

Production main entry: **https://sportifyplus.de/only-talents**

Optional host: `talents.sportifyplus.de` (often a redirect gateway into main-domain `/only-talents/*`).

## Context switching

`App\Support\OnlyTalentsContext` + config keys:

```env
OT_HOST=talents.sportifyplus.de
OT_URL=https://talents.sportifyplus.de
OT_MAIN_URL=https://sportifyplus.de
SESSION_DOMAIN=.sportifyplus.de
```

When `HTTP_HOST` matches `OT_HOST`, `App::run()` loads [`config/routes-onlytalents.php`](../config/routes-onlytalents.php) (paths at domain root: `/`, `/discover`, …).

On the **main domain**, the same features are mounted under `/only-talents/…` in the main routes file.

## Main-domain routes (canonical)

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/only-talents` | Immersive shorts home (`tiktokLanding`) |
| GET | `/only-talents/home` | **301** → `/only-talents` (legacy) |
| GET | `/only-talents/discover` | Discovery browse |
| GET | `/only-talents/categories` | Category index |
| GET | `/only-talents/categories/{slug}` | Category detail (+ shorts where applicable) |
| GET | `/only-talents/locations` | Locations |
| GET | `/only-talents/videos` | Videos listing |
| GET | `/only-talents/profile/edit` | Edit OT profile |
| POST | `/only-talents/profile/edit` | Save profile |
| GET | `/only-talents/profile/{slug}` | Public talent profile |
| GET | `/only-talents/upload` | Upload form |
| POST | `/only-talents/upload` | Upload short |
| GET | `/only-talents/api/load-more` | Pagination / infinite scroll JSON |
| POST | `/only-talents/shorts/{id}/like` | Like a short |
| GET/POST | `/only-talents/communities…` | Communities, posts, courses, live |

Handlers: `OnlyTalentsDiscoveryController`, `OnlyTalentsController`, `OtCommunityController`.

## Subdomain routes

Same controllers; paths without the `/only-talents` prefix (e.g. `/` = home feed, `/api/shorts/load-more`, `/shorts/{id}/like`).

## Shorts feed (TikTok-style)

Home action builds a vertical feed:

- View: `views/pages/onlytalents/home-20260703.php`
- Layout: `views/layouts/onlytalents.php`
- Flag: `tiktokLanding => true`
- Data: `OtLandingShortsService::browse($filter, $page, …)`
- Filters / category query params supported
- Sync metadata from `TalentShortsService`

CSS: `public/assets/css/onlytalents.css`.

Legacy `/only-talents/home` permanently redirects to `/only-talents` (query string preserved).

## Auth

OT pages share Sportify sessions. Login/register on main domain (or OT host `/login` when subdomain serves the app). After auth, users return via redirect params used by discover/CTA links.

Google OAuth remains on the main app flow (`/auth/google`) — see [auth.md](auth.md).

## Ops / deploy notes

- Bootstrap may `opcache_invalidate` OT-related files when host is `talents.*` / `onlytalents.*`.
- Prefer deploying OT assets to the **live** `domains/sportifyplus.de/public_html` tree (see [deployment-hostinger.md](deployment-hostinger.md)).
- Scripts such as `scripts/deploy_ot_home_immersive_20260707.sh` upload CSS + home view + controller; use env FTP password.

## Related

- [architecture.md](architecture.md)
- [deployment-hostinger.md](deployment-hostinger.md)
- README key URLs
