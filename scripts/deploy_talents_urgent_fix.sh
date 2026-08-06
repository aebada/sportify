#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"

upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${lfile}" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}

USERINI_MIN="${ROOT}/public/.user.ini-talents-minimal"
cat > "$USERINI_MIN" << 'INI'
opcache.validate_timestamps=1
opcache.revalidate_freq=0
INI

TALENT_BASES=(
  "talents"
  "domains/sportifyplus.de/public_html/talents"
  "domains/talents.sportifyplus.de/public_html"
)

CORE=(
  "public/index-onlytalents-production.php:index.php"
  "public/.htaccess-onlytalents-production:.htaccess"
  "public/site-prepend-20260705-ot.php:site-prepend-20260705-ot.php"
  "config/routes-onlytalents.php:config/routes-onlytalents.php"
  "bootstrap.php:bootstrap.php"
  "bootstrap-20260705-ot.php:bootstrap-20260705-ot.php"
  "app/Support/OnlyTalentsContext.php:app/Support/OnlyTalentsContext.php"
  "app/Support/ot_helpers_hotfix.php:app/Support/ot_helpers_hotfix.php"
  "app/Support/helpers.php:app/Support/helpers.php"
  "app/Support/helpers-20260705.php:app/Support/helpers-20260705.php"
  "views/partials/ot-nav.php:views/partials/ot-nav.php"
  "app/Controllers/OnlyTalentsDiscoveryController.php:app/Controllers/OnlyTalentsDiscoveryController.php"
  "views/pages/onlytalents/home-20260703.php:views/pages/onlytalents/home-20260703.php"
  "public/ot-opcache-flush-20260702.php:ot-opcache-flush-20260702.php"
)

for base in "${TALENT_BASES[@]}"; do
  echo "=== ${base} ==="
  for pair in "${CORE[@]}"; do
    upload "${pair%%:*}" "${base}/${pair#*:}"
  done
  upload "public/.user.ini-talents-minimal" "${base}/.user.ini"
done

echo "=== account root .htaccess ==="
upload "public/.htaccess-account-root-talents-20260705" ".htaccess"

echo "=== main public_html site-prepend (no OT redirect) ==="
upload "public/site-prepend-20260705-ot.php" "domains/sportifyplus.de/public_html/site-prepend-20260705-ot.php"

echo "=== opcache purge ==="
curl -s 'https://sportifyplus.de/opcache-purge.php' | head -3 || true
curl -s 'https://talents.sportifyplus.de/ot-opcache-flush-20260702.php?key=ot-flush-20260702' | head -3 || true

echo "=== verify ==="
curl -sI 'https://talents.sportifyplus.de/' | head -12
curl -sL 'https://talents.sportifyplus.de/' | head -c 2000
