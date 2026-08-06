#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"
BASES=(
  ""
  "domains/sportifyplus.de/public_html"
)

upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${lfile}" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}

echo "=== OnlyTalents immersive home v5 (20260707) ==="

FILES=(
  "public/assets/css/onlytalents.css:public/assets/css/onlytalents.css"
  "public/assets/css/onlytalents.css:assets/css/onlytalents.css"
  "views/pages/onlytalents/home-20260703.php:views/pages/onlytalents/home-20260703.php"
  "views/pages/onlytalents/home.php:views/pages/onlytalents/home.php"
  "views/layouts/onlytalents.php:views/layouts/onlytalents.php"
  "app/Support/helpers.php:app/Support/helpers.php"
  "app/Controllers/OnlyTalentsDiscoveryController.php:app/Controllers/OnlyTalentsDiscoveryController.php"
  "config/routes.php:config/routes.php"
  "config/routes-20260621-memberships-v1.php:config/routes-20260621-memberships-v1.php"
  "config/routes-20260629-memberships-wellness-v1.php:config/routes-20260629-memberships-wellness-v1.php"
  "public/opcache-purge-ot-immersive-v5.php:public/opcache-purge-ot-immersive-v5.php"
  "public/opcache-purge-ot-immersive-v5.php:opcache-purge-ot-immersive-v5.php"
  "public/opcache-purge.php:public/opcache-purge.php"
  "public/opcache-purge.php:opcache-purge.php"
)

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

echo "=== opcache purge ==="
curl -s 'https://sportifyplus.de/opcache-purge-ot-immersive-v5.php' | head -15 || true
curl -s 'https://sportifyplus.de/opcache-purge.php' | head -3 || true

echo "=== verify ==="
curl -s "https://sportifyplus.de/only-talents?verify=$(date +%s)" | grep -oE 'deploy-marker:[^<]+|ot-shorts-immersive-v5|ot-filters--immersive|ot-landing-shorts' | head -8 || true
