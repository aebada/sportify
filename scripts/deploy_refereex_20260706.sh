#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"
BASE="domains/sportifyplus.de/public_html"

upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  if curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${lfile}" "ftp://${HOST}/${remote}" >/dev/null; then
    echo OK
  else
    echo FAIL
    return 1
  fi
}

echo "=== RefereeX full deploy $(date -u +%Y%m%dT%H%M%SZ) ==="

# Root routing + site-prepend (account-root path)
upload "public/.htaccess-root-refereex-php" "${BASE}/.htaccess"
upload "public/site-prepend-20260706-refereex.php" "${BASE}/site-prepend-20260706-refereex.php"
upload "public/site-prepend-20260706-refereex.php" "${BASE}/public/site-prepend-20260706-refereex.php"
upload "public/site-prepend-20260705-refereex-live.php" "${BASE}/site-prepend-20260705-refereex-live.php"
upload "public/.user.ini-refereex-account-root" "${BASE}/.user.ini"
upload "public/.user.ini-refereex-account-root" "${BASE}/public/.user.ini"

# Public front controllers
upload "public/index.php" "${BASE}/public/index.php"
upload "public/index-20260704-refereex-v1.php" "${BASE}/public/index-20260704-refereex-v1.php"
upload "public/refereex-ai.php" "${BASE}/public/refereex-ai.php"
upload "public/refereex-ai.php" "${BASE}/refereex-ai.php"
upload "public/.htaccess" "${BASE}/public/.htaccess"
upload "public/opcache-reset-refereex.php" "${BASE}/public/opcache-reset-refereex.php"
upload "public/opcache-purge.php" "${BASE}/public/opcache-purge.php"

upload "bootstrap-20260621-authfix.php" "${BASE}/bootstrap-20260621-authfix.php"
upload "app/Controllers/RefereeXController.php" "${BASE}/app/Controllers/RefereeXController.php"
upload "app/Controllers/TvScheduleController.php" "${BASE}/app/Controllers/TvScheduleController.php"
upload "app/Services/LiveHd7SyncService.php" "${BASE}/app/Services/LiveHd7SyncService.php"
upload "config/routes-refereex-v1.php" "${BASE}/config/routes-refereex-v1.php"
upload "config/routes-20260621-memberships-v1.php" "${BASE}/config/routes-20260621-memberships-v1.php"

# Views
upload "views/pages/refereex-ai/index.php" "${BASE}/views/pages/refereex-ai/index.php"
upload "views/partials/home-refereex.php" "${BASE}/views/partials/home-refereex.php"
upload "views/partials/header-brand.php" "${BASE}/views/partials/header-brand.php"
upload "views/errors/404.php" "${BASE}/views/errors/404.php"

# Assets
upload "public/assets/css/refereex-ai.css" "${BASE}/public/assets/css/refereex-ai.css"
upload "public/assets/js/refereex-ai.js" "${BASE}/public/assets/js/refereex-ai.js"
upload "lang/en/refereex-messages.php" "${BASE}/lang/en/refereex-messages.php"

# Router fallback (if local copy exists)
if [[ -f "${ROOT}/.ftp-deploy/app/Core/Router.php" ]]; then
  upload ".ftp-deploy/app/Core/Router.php" "${BASE}/app/Core/Router.php"
fi

# Home page patch endpoint
upload "public/patch-home-refereex-20260706.php" "${BASE}/public/patch-home-refereex-20260706.php"

# Deploy marker
echo "refereex-php-v20260706-$(date -u +%H%M%S)" > "${ROOT}/public/deploy-marker-refereex.txt"
upload "public/deploy-marker-refereex.txt" "${BASE}/public/deploy-marker-refereex.txt"

echo "=== HTTP verify ==="
sleep 4
for u in \
  'https://sportifyplus.de/opcache-reset-refereex.php' \
  'https://sportifyplus.de/refereex-ai.php' \
  'https://sportifyplus.de/refereex-ai' \
  'https://sportifyplus.de/' \
  'https://sportifyplus.de/live-sports' \
  'https://sportifyplus.de/fit-pass'; do
  echo "--- $u ---"
  curl -sSI "$u" 2>/dev/null | grep -E 'HTTP/|x-sportify|content-type' | head -4 || true
done
echo "--- body check ---"
curl -sS 'https://sportifyplus.de/refereex-ai.php' 2>/dev/null | grep -E 'refereex-page|section-head|RefereeX AI|404' | head -5 || true
curl -sS 'https://sportifyplus.de/refereex-ai' 2>/dev/null | grep -E 'refereex-page|section-head|RefereeX AI|404' | head -5 || true
