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

BASES=(
  ""
  "domains/sportifyplus.de/public_html"
)

FILES=(
  "config/routes.php:config/routes.php"
  "config/routes-20260621-memberships-v1.php:config/routes-20260621-memberships-v1.php"
  "app/Controllers/OnlyTalentsDiscoveryController.php:app/Controllers/OnlyTalentsDiscoveryController.php"
  "app/Support/helpers.php:app/Support/helpers.php"
  "public/site-prepend-20260705-ot.php:site-prepend-20260705-ot.php"
  "public/opcache-purge.php:public/opcache-purge.php"
  "public/opcache-purge.php:opcache-purge.php"
)

DOMAINS_ONLY=(
  "public/.htaccess-domains-live-20260707:.htaccess"
)

echo "=== OnlyTalents canonical URL /only-talents (20260707) ==="
for base in "${BASES[@]}"; do
  echo "--- ${base:-account-root} ---"
  for pair in "${FILES[@]}"; do
    lfile="${pair%%:*}"
    remote="${pair#*:}"
    if [[ -n "$base" ]]; then
      upload "${lfile}" "${base}/${remote}"
    else
      upload "${lfile}" "${remote}"
    fi
  done
done

echo "--- domains htaccess ---"
for pair in "${DOMAINS_ONLY[@]}"; do
  upload "${pair%%:*}" "domains/sportifyplus.de/public_html/${pair#*:}"
done

echo "=== opcache purge ==="
curl -s 'https://sportifyplus.de/opcache-purge.php?bust='"$(date +%s)" | head -8 || true
curl -s 'https://sportifyplus.de/deploy-check.php?ot_home_purge=20260707' | tail -3 || true

echo "=== verify ==="
curl -sI 'https://sportifyplus.de/only-talents/home' | grep -iE 'HTTP/|location:' || true
curl -sI 'https://sportifyplus.de/only-talents' | head -3 || true
curl -s 'https://sportifyplus.de/only-talents' | grep -oE 'href="[^"]*only-talents[^"]*"' | head -5 || true
