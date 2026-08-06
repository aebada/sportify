#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"
BASE="domains/sportifyplus.de/public_html"
MARKER="$(date -u +%Y%m%dT%H%M%SZ)"

upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${lfile}" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}

echo "=== RefereeX deploy ${MARKER} → ${BASE} ==="

php "${ROOT}/scripts/build-refereex-static-full.php"

upload "public/refereex-ai.html" "${BASE}/public/refereex-ai.html"
upload "public/refereex-ai-static.html" "${BASE}/public/refereex-ai-static.html"
upload "public/.htaccess-domains-root-refereex-20260708" "${BASE}/.htaccess"
upload "public/.htaccess-domains-public-refereex-20260708" "${BASE}/public/.htaccess"
upload "app/Controllers/RefereeXController.php" "${BASE}/app/Controllers/RefereeXController.php"
upload "app/Controllers/FitPassController.php" "${BASE}/app/Controllers/FitPassController.php"
upload "views/pages/refereex-ai/index.php" "${BASE}/views/pages/refereex-ai/index.php"
upload "views/pages/fitpass.php" "${BASE}/views/pages/fitpass.php"
upload "views/partials/header-brand.php" "${BASE}/views/partials/header-brand.php"
upload "public/assets/css/refereex-ai.css" "${BASE}/public/assets/css/refereex-ai.css"
upload "public/assets/js/refereex-ai.js" "${BASE}/public/assets/js/refereex-ai.js"
upload "lang/en/refereex-messages.php" "${BASE}/lang/en/refereex-messages.php"
upload "lang/de/refereex-messages.php" "${BASE}/lang/de/refereex-messages.php"
upload "lang/ar/refereex-messages.php" "${BASE}/lang/ar/refereex-messages.php"
upload "public/site-prepend-20260705-refereex-static.php" "${BASE}/site-prepend-20260705-refereex-static.php"

echo "=== HTTP verify ==="
sleep 3
for u in \
  'https://sportifyplus.de/refereex-ai' \
  'https://sportifyplus.de/refereex-ai.html' \
  'https://sportifyplus.de/fit-pass?view=refereex' \
  'https://sportifyplus.de/live-sports.html'; do
  echo "--- $u ---"
  curl -sSI --max-time 20 "$u" | grep -E 'HTTP/|content-type|x-sportify|x-powered' || true
  curl -sS --max-time 20 "$u" | grep -iE 'RefereeX|rx-hero|refereex-page|deploy-marker:refereex' | head -3 || true
done
