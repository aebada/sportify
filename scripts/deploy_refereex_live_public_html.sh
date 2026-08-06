#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"
BASE="public_html"

upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${lfile}" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}

echo "=== RefereeX deploy to LIVE public_html ==="

upload "public/index.php" "${BASE}/public/index.php"
upload "public/index-20260704-refereex-v1.php" "${BASE}/public/index-20260704-refereex-v1.php"
upload "public/refereex-ai.php" "${BASE}/public/refereex-ai.php"
upload "public/opcache-reset-refereex.php" "${BASE}/public/opcache-reset-refereex.php"
upload "public/rx-direct-probe-20260705.php" "${BASE}/public/rx-direct-probe-20260705.php"
upload "public/.htaccess" "${BASE}/public/.htaccess"
upload "public/.htaccess-root-refereex-php" "${BASE}/.htaccess"
upload "public/site-prepend-20260705-refereex-live.php" "${BASE}/site-prepend-20260705-refereex-live.php"
upload "public/.user.ini-refereex-php" "${BASE}/.user.ini"
upload "public/.user.ini-refereex-php" "${BASE}/public/.user.ini"
upload ".ftp-deploy/bootstrap-20260621-authfix.php" "${BASE}/bootstrap-20260621-authfix.php"
upload "app/Controllers/RefereeXController.php" "${BASE}/app/Controllers/RefereeXController.php"
upload "config/routes-refereex-v1.php" "${BASE}/config/routes-refereex-v1.php"
upload "views/pages/refereex-ai/index.php" "${BASE}/views/pages/refereex-ai/index.php"
upload "views/errors/404.php" "${BASE}/views/errors/404.php"
upload "public/assets/css/refereex-ai.css" "${BASE}/public/assets/css/refereex-ai.css"
upload "public/assets/js/refereex-ai.js" "${BASE}/public/assets/js/refereex-ai.js"
echo "refereex-php-live-$(date -u +%Y%m%dT%H%M%SZ)" > "${ROOT}/public/deploy-marker-refereex.txt"
upload "public/deploy-marker-refereex.txt" "${BASE}/public/deploy-marker-refereex.txt"

echo "=== verify FTP ==="
curl -sS --ftp-pasv -u "$AUTH" "ftp://${HOST}/${BASE}/public/index.php" | head -3

echo "=== HTTP verify ==="
sleep 3
for u in \
  'https://sportifyplus.de/rx-direct-probe-20260705.php' \
  'https://sportifyplus.de/refereex-boot-probe' \
  'https://sportifyplus.de/refereex-ai' \
  'https://sportifyplus.de/refereex-ai.php' \
  'https://sportifyplus.de/opcache-reset-refereex.php' \
  'https://sportifyplus.de/' \
  'https://sportifyplus.de/live-sports' \
  'https://sportifyplus.de/fit-pass'; do
  echo "--- $u ---"
  curl -sSI "$u" | grep -E 'HTTP/|x-sportify|content-type' || true
done
curl -sS 'https://sportifyplus.de/refereex-ai' | grep -E 'RefereeX AI|rx-hero|refereex-page' | head -3 || true
