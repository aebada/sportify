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
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${lfile}" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}

echo "=== RefereeX deploy $(date -u +%Y%m%dT%H%M%SZ) → ${BASE} ==="

php "${ROOT}/scripts/build-refereex-live-sports-html.php"

upload "public/refereex-ai.html" "${BASE}/public/refereex-ai.html"
upload "public/.htaccess-domains-root-refereex-20260708" "${BASE}/.htaccess"
upload "public/.htaccess-domains-public-refereex-20260708" "${BASE}/public/.htaccess"
upload "public/index-20260708-refereex-live.php" "${BASE}/public/index-20260708-refereex-live.php"
upload "public/site-prepend-20260705-refereex-static.php" "${BASE}/site-prepend-20260705-refereex-static.php"
upload "app/Controllers/RefereeXController.php" "${BASE}/app/Controllers/RefereeXController.php"
upload "app/Controllers/FitPassController.php" "${BASE}/app/Controllers/FitPassController.php"
upload "app/Controllers/TvScheduleController.php" "${BASE}/app/Controllers/TvScheduleController.php"
upload "config/routes-refereex-v1.php" "${BASE}/config/routes-refereex-v1.php"
upload "views/pages/refereex-ai/index.php" "${BASE}/views/pages/refereex-ai/index.php"
upload "views/partials/header-brand.php" "${BASE}/views/partials/header-brand.php"
upload "public/assets/css/refereex-ai.css" "${BASE}/public/assets/css/refereex-ai.css"
upload "public/assets/js/refereex-ai.js" "${BASE}/public/assets/js/refereex-ai.js"

echo "=== HTTP verify ==="
sleep 2
for u in \
  'https://sportifyplus.de/refereex-ai.html' \
  'https://sportifyplus.de/refereex-ai' \
  'https://sportifyplus.de/fit-pass?view=refereex' \
  'https://sportifyplus.de/' \
  'https://sportifyplus.de/fit-pass' \
  'https://sportifyplus.de/live-sports'; do
  echo "--- $u ---"
  curl -sSI --max-time 15 "$u" | grep -E 'HTTP/|content-type|x-sportify|x-powered' || true
done

echo "=== Body markers ==="
curl -sS --max-time 15 'https://sportifyplus.de/refereex-ai.html' | grep -iE 'RefereeX|rx-hero|refereex-page|deploy-marker' | head -5 || true
curl -sS --max-time 15 'https://sportifyplus.de/refereex-ai' | grep -iE 'RefereeX|rx-hero|refereex-page|deploy-marker' | head -5 || true
