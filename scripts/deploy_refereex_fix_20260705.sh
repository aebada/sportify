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

echo "=== RefereeX PHP routing deploy 20260705 ==="

# Front controller + early handlers
upload "public/index.php" "${BASE}/public/index.php"
upload "public/index-20260704-refereex-v1.php" "${BASE}/public/index-20260704-refereex-v1.php"
upload "public/refereex-ai.php" "${BASE}/public/refereex-ai.php"
upload "public/opcache-reset-refereex.php" "${BASE}/public/opcache-reset-refereex.php"
upload "public/.htaccess" "${BASE}/public/.htaccess"

# Root routing + prepend
upload "public/.htaccess-root-refereex-php" "${BASE}/.htaccess"
upload "public/site-prepend-20260705-refereex-live.php" "${BASE}/site-prepend-20260705-refereex-live.php"
upload "public/.user.ini-refereex-php" "${BASE}/.user.ini"
upload "public/.user.ini-refereex-php" "${BASE}/public/.user.ini"

# RefereeX app files
upload "app/Controllers/RefereeXController.php" "${BASE}/app/Controllers/RefereeXController.php"
upload "config/routes-refereex-v1.php" "${BASE}/config/routes-refereex-v1.php"
upload "views/pages/refereex-ai/index.php" "${BASE}/views/pages/refereex-ai/index.php"
upload "views/errors/404.php" "${BASE}/views/errors/404.php"
upload "public/assets/css/refereex-ai.css" "${BASE}/public/assets/css/refereex-ai.css"
upload "public/assets/js/refereex-ai.js" "${BASE}/public/assets/js/refereex-ai.js"

# Deploy marker for verification
echo "refereex-php-v20260705-$(date -u +%Y%m%dT%H%M%SZ)" > "${ROOT}/public/deploy-marker-refereex.txt"
upload "public/deploy-marker-refereex.txt" "${BASE}/public/deploy-marker-refereex.txt"

echo "=== OPcache reset ==="
curl -sS 'https://sportifyplus.de/opcache-purge.php' | head -5 || true
curl -sS 'https://sportifyplus.de/opcache-reset-refereex.php' | head -10 || true

echo "=== Verify URLs ==="
for u in \
  'https://sportifyplus.de/' \
  'https://sportifyplus.de/live-sports' \
  'https://sportifyplus.de/fit-pass' \
  'https://sportifyplus.de/refereex-ai' \
  'https://sportifyplus.de/refereex-boot-probe' \
  'https://sportifyplus.de/refereex-ai.php' \
  'https://sportifyplus.de/opcache-reset-refereex.php?serve=1'; do
  echo "--- $u ---"
  curl -sSI "$u" | grep -E 'HTTP/|x-sportify|x-powered|content-type|x-hcdn' || true
done

echo "=== Body markers ==="
curl -sS 'https://sportifyplus.de/refereex-ai' | grep -E 'refereex-ai-v1|RefereeX AI|rx-hero|X-Sportify' | head -5 || true
