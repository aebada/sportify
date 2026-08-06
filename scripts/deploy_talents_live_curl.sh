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
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${local}" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}
FILES=(
  "public/index-onlytalents.html:index.html"
  "public/.htaccess-onlytalents:.htaccess"
  "public/index-onlytalents-redirect.php:index.php"
  "public/site-prepend-20260702-ot.php:site-prepend-20260702-ot.php"
  "public/.user.ini-talents:.user.ini"
  "public/ot-opcache-flush-20260702.php:ot-opcache-flush-20260702.php"
  "app/Support/ot_helpers_hotfix.php:app/Support/ot_helpers_hotfix.php"
  "app/Support/helpers-20260702-ot.php:app/Support/helpers-20260702-ot.php"
  "app/Support/helpers.php:app/Support/helpers.php"
  "bootstrap-20260629-ot.php:bootstrap-20260629-ot.php"
  "bootstrap-20260702-ot.php:bootstrap-20260702-ot.php"
  "bootstrap.php:bootstrap.php"
  "views/pages/onlytalents/home-20260701.php:views/pages/onlytalents/home-20260701.php"
  "app/Controllers/OnlyTalentsDiscoveryController.php:app/Controllers/OnlyTalentsDiscoveryController.php"
  "public/site-prepend-20260621-patched.php:../site-prepend-20260621.php"
)
for base in talents domains/sportifyplus.de/public_html/talents; do
  echo "=== $base ==="
  for pair in "${FILES[@]}"; do
    local="${pair%%:*}"
    remote="${pair#*:}"
    if [[ "$remote" == ../* ]]; then
      upload "$local" "domains/sportifyplus.de/public_html/${remote#../}"
      upload "$local" "public_html/${remote#../}"
      continue
    fi
    upload "$local" "${base}/${remote}"
  done
done
for base in public_html/public domains/sportifyplus.de/public_html/public; do
  upload public/push-ot-talents-live-20260701.php "${base}/push-ot-talents-live-20260701.php"
done
