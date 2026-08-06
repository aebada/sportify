#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"


dele_remote() {
  local remote="$1"
  printf 'DELE %s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 60 --ftp-pasv     -u "$AUTH" "ftp://${HOST}/${remote}" -Q "DELE ${remote}" && echo OK || echo SKIP
}

upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${lfile}" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}

ACCOUNT_ROOT=""
TALENT_BASES=(
  ""
  "talents"
  "domains/sportifyplus.de/public_html/talents"
  "domains/talents.sportifyplus.de/public_html"
)

# Layer 1–3: Apache + static + minimal PHP (subdomain docroots)
GATEWAY=(
  "public/index-onlytalents.html:index.html"
  "public/deploy-marker-20260705.txt:deploy-marker-20260705.txt"
  "public/.htaccess-onlytalents:.htaccess"
  "public/index-20260705.php:index-20260705.php"
  "public/index-onlytalents-redirect.php:index-onlytalents-redirect.php"
)

# Layer 4: full app + prepend + fresh OPcache-bypass filenames
APP=(
  "public/index-onlytalents-production.php:index-onlytalents-production.php"
  "public/index-20260705-ot-live.php:index-20260705-ot-live.php"
  "app/Support/ot-prepend-20260705.php:ot-prepend-20260705.php"
  "public/site-prepend-20260705-ot.php:site-prepend-20260705-ot.php"
  "app/Support/ot_helpers_hotfix.php:app/Support/ot_helpers_hotfix.php"
  "app/Support/helpers-20260705.php:app/Support/helpers-20260705.php"
  "app/Support/helpers.php:app/Support/helpers.php"
  "app/Support/helpers-20260702-ot.php:app/Support/helpers-20260702-ot.php"
  "app/Support/helpers-20260703-ot-live.php:app/Support/helpers-20260703-ot-live.php"
  "app/Support/OnlyTalentsContext.php:app/Support/OnlyTalentsContext.php"
  "bootstrap-20260705-ot.php:bootstrap-20260705-ot.php"
  "bootstrap-20260703-ot.php:bootstrap-20260703-ot.php"
  "bootstrap-20260702-ot.php:bootstrap-20260702-ot.php"
  "bootstrap-20260629-ot.php:bootstrap-20260629-ot.php"
  "app/Controllers/OnlyTalentsDiscoveryController.php:app/Controllers/OnlyTalentsDiscoveryController.php"
  "views/pages/onlytalents/home-20260701.php:views/pages/onlytalents/home-20260701.php"
  "public/ot-opcache-flush-20260702.php:ot-opcache-flush-20260702.php"
  "public/ot-fatal-probe-20260705.php:ot-fatal-probe-20260705.php"
)

for base in "${TALENT_BASES[@]}"; do
  echo "=== $base (gateway) ==="
  for pair in "${GATEWAY[@]}"; do
    if [[ -z "$base" ]]; then remote="${pair#*:}"; else remote="${base}/${pair#*:}"; fi
    upload "${pair%%:*}" "$remote"
  done
  echo "=== $base (app) ==="
  for pair in "${APP[@]}"; do
    if [[ -z "$base" ]]; then remote="${pair#*:}"; else remote="${base}/${pair#*:}"; fi
    upload "${pair%%:*}" "$remote"
  done
  if [[ "$base" == "domains/talents.sportifyplus.de/public_html" ]]; then
    upload "public/.user.ini-talents-minimal" "${base}/.user.ini"
  elif [[ "$base" == "talents" ]]; then
    upload "public/.user.ini-talents-minimal" "${base}/.user.ini"
  else
    upload "public/.user.ini-talents-minimal" "${base}/.user.ini"
  fi
  if [[ -n "$base" ]]; then
    dele_remote "${base}/index.php"
  else
    dele_remote "index.php"
  fi
done

echo "=== main public_html site-prepend + .user.ini ==="
upload "public/site-prepend-20260705-ot.php" "domains/sportifyplus.de/public_html/site-prepend-20260705-ot.php"
upload "public/site-prepend-20260705-ot.php" "public_html/site-prepend-20260705-ot.php"
upload "public/.user.ini-production" "domains/sportifyplus.de/public_html/.user.ini"
upload "public/.user.ini-production" "public_html/.user.ini"

echo "=== verify HTTP ==="
curl -sI 'https://talents.sportifyplus.de/' | head -12
curl -sI 'https://talents.sportifyplus.de/index.html' | head -12
curl -sI 'https://talents.sportifyplus.de/index-20260705.php' | head -12
curl -sI 'https://talents.sportifyplus.de/home' | head -12
