#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"
upload() {
  local local="$1" remote="$2"
  printf '%s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 120 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${local}" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}
# Minimal .user.ini — opcache only (no auto_prepend fatal)
USERINI_MIN="${ROOT}/public/.user.ini-talents-minimal"
cat > "$USERINI_MIN" << 'INI'
opcache.validate_timestamps=1
opcache.revalidate_freq=0
INI
BASES=(
  "talents"
  "domains/sportifyplus.de/public_html/talents"
  "domains/talents.sportifyplus.de/public_html"
)
FILES=(
  "public/index-onlytalents-production.php:index.php"
  "public/.htaccess-onlytalents:.htaccess"
  "bootstrap-20260703-ot.php:bootstrap-20260703-ot.php"
  "bootstrap-20260629-ot.php:bootstrap-20260629-ot.php"
  "bootstrap-20260702-ot.php:bootstrap-20260702-ot.php"
  "app/Support/OnlyTalentsContext.php:app/Support/OnlyTalentsContext.php"
  "app/Support/helpers.php:app/Support/helpers.php"
  "app/Support/helpers-20260629.php:app/Support/helpers-20260629.php"
  "app/Support/helpers-20260702-ot.php:app/Support/helpers-20260702-ot.php"
  "app/Support/helpers-20260703-ot-live.php:app/Support/helpers-20260703-ot-live.php"
  "app/Support/ot_helpers_hotfix.php:app/Support/ot_helpers_hotfix.php"
  "app/Controllers/OnlyTalentsDiscoveryController.php:app/Controllers/OnlyTalentsDiscoveryController.php"
  "views/pages/onlytalents/home-20260701.php:views/pages/onlytalents/home-20260701.php"
  "public/site-prepend-20260702-talents-gateway.php:site-prepend-20260702-talents-gateway.php"
  "public/site-prepend-20260629-ot.php:site-prepend-20260629-ot.php"
  "public/ot-opcache-flush-20260702.php:ot-opcache-flush-20260702.php"
)
for base in "${BASES[@]}"; do
  echo "=== $base ==="
  for pair in "${FILES[@]}"; do
    upload "${pair%%:*}" "${base}/${pair#*:}"
  done
  upload "public/.user.ini-talents-minimal:.user.ini"
done
# Also talents/public mirror
for base in "talents/public" "domains/sportifyplus.de/public_html/talents/public"; do
  echo "=== $base ==="
  for pair in "${FILES[@]}"; do
    upload "${pair%%:*}" "${base}/${pair#*:}"
  done
  upload "public/.user.ini-talents-minimal:.user.ini"
done
echo "=== opcache triggers ==="
curl -s 'https://sportifyplus.de/opcache-purge.php' | head -3
curl -s 'https://talents.sportifyplus.de/opcache-purge.php' | head -3 || true
curl -s 'https://talents.sportifyplus.de/ot-opcache-flush-20260702.php?key=ot-flush-20260702' | head -3 || true
curl -sI 'https://talents.sportifyplus.de/' | head -10
