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

TALENT_BASES=(
  "domains/sportifyplus.de/public_html/talents"
  "talents"
  "domains/talents.sportifyplus.de/public_html"
)

GATEWAY=(
  "public/index-onlytalents.html:index.html"
  "public/.htaccess-talents-subdomain-live-20260706:.htaccess"
  "public/index-onlytalents-redirect.php:index.php"
  "public/site-prepend-20260705-ot.php:site-prepend-20260705-ot.php"
)

for base in "${TALENT_BASES[@]}"; do
  echo "=== ${base} ==="
  for pair in "${GATEWAY[@]}"; do
    upload "${pair%%:*}" "${base}/${pair#*:}"
  done
  if [[ "$base" == "domains/talents.sportifyplus.de/public_html" ]]; then
    upload "public/.user.ini-talents-subdomain-gateway-20260706" "${base}/.user.ini"
  else
    upload "public/.user.ini-talents-gateway-20260706" "${base}/.user.ini"
  fi
done

upload "public/push-talents-gateway-20260706.php" "domains/sportifyplus.de/public_html/public/push-talents-gateway-20260706.php"
upload "public/push-talents-gateway-20260706.php" "domains/sportifyplus.de/public_html/push-talents-gateway-20260706.php"
upload "public/push-talents-gateway-20260706.php" "domains/sportifyplus.de/public_html/talents/push-talents-gateway-20260706.php"
upload "public/push-talents-gateway-20260706.php" "domains/talents.sportifyplus.de/public_html/push-talents-gateway-20260706.php"

echo "=== main site prepend + user.ini ==="
upload "public/site-prepend-20260705-ot.php" "domains/sportifyplus.de/public_html/site-prepend-20260705-ot.php"
upload "public/.user.ini-production" "domains/sportifyplus.de/public_html/.user.ini"
upload "public/site-prepend-20260705-ot.php" "public_html/site-prepend-20260705-ot.php"
upload "public/.user.ini-production" "public_html/.user.ini"

echo "=== opcache purge ==="
curl -s 'https://sportifyplus.de/opcache-purge.php' | head -3 || true
curl -s 'https://sportifyplus.de/public/opcache-purge.php' | head -3 || true

echo "=== push gateway on server ==="
curl -s 'https://sportifyplus.de/push-talents-gateway-20260706.php?key=talents-gateway-20260706' || true
curl -s 'https://sportifyplus.de/public/push-talents-gateway-20260706.php?key=talents-gateway-20260706' || true
curl -s 'https://talents.sportifyplus.de/deploy-check.php?push=talents-gateway-20260706' 2>/dev/null | head -3 || true

echo "=== verify ==="
for u in \
  'https://talents.sportifyplus.de/' \
  'https://talents.sportifyplus.de/discover' \
  'https://talents.sportifyplus.de/login' \
  'https://sportifyplus.de/only-talents/home'; do
  echo "$u"
  curl -sI "$u" | head -8
  echo
done
