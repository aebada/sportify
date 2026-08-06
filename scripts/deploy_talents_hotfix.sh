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
BASES=(
  "talents:public/.user.ini-talents-root:.user.ini"
  "domains/sportifyplus.de/public_html/talents:public/.user.ini-talents-nested:.user.ini"
  "domains/talents.sportifyplus.de/public_html:public/.user.ini-talents-subdomain:.user.ini"
)
COMMON=(
  "public/index-onlytalents.html:index.html"
  "public/.htaccess-onlytalents:.htaccess"
  "public/index-onlytalents-redirect.php:index.php"
  "public/site-prepend-20260702-talents-gateway.php:site-prepend-20260702-talents-gateway.php"
  "public/site-prepend-20260702-ot.php:site-prepend-20260702-ot.php"
  "public/ot-opcache-flush-20260702.php:ot-opcache-flush-20260702.php"
  "bootstrap-20260702-ot.php:bootstrap-20260702-ot.php"
  "app/Support/ot_helpers_hotfix.php:app/Support/ot_helpers_hotfix.php"
  "app/Support/helpers-20260702-ot.php:app/Support/helpers-20260702-ot.php"
  "app/Support/helpers.php:app/Support/helpers.php"
)
for entry in "${BASES[@]}"; do
  base="${entry%%:*}"
  rest="${entry#*:}"
  userini="${rest%%:*}"
  userini_remote="${rest#*:}"
  echo "=== $base ==="
  for pair in "${COMMON[@]}"; do
    local="${pair%%:*}"
    remote="${pair#*:}"
    upload "$local" "${base}/${remote}"
  done
  upload "$userini" "${base}/${userini_remote}"
done
curl -sI "https://talents.sportifyplus.de/" | head -8

PUBLIC_BASES=(
  "talents/public:public/.user.ini-talents-root:.user.ini"
  "domains/sportifyplus.de/public_html/talents/public:public/.user.ini-talents-nested:.user.ini"
)
for entry in "${PUBLIC_BASES[@]}"; do
  base="${entry%%:*}"
  rest="${entry#*:}"
  userini="${rest%%:*}"
  userini_remote="${rest#*:}"
  echo "=== $base ==="
  for pair in "${COMMON[@]}"; do
    local="${pair%%:*}"
    remote="${pair#*:}"
    upload "$local" "${base}/${remote}"
  done
  upload "$userini" "${base}/${userini_remote}"
done
