#!/usr/bin/env bash
set -euo pipefail
export PATH="/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin:$PATH"
HOST="92.113.19.130"
USER="u234903558.sportifywebsite"
# password via env to reduce inline secret exposure in process list copies
PASS="${SPORTIFY_FTP_PASS:-}"
if [[ -z "$PASS" ]]; then
  echo "Set SPORTIFY_FTP_PASS" >&2
  exit 1
fi
AUTH="${USER}:${PASS}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "$lfile" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}

for BASE in "domains/sportifyplus.de/public_html" "" "public_html"; do
  PREFIX="${BASE:+$BASE/}"
  echo "=== $BASE ==="
  upload "$ROOT/site-prepend-20260720-auth-brand.php" "${PREFIX}site-prepend-20260720-auth-brand.php"
  upload "$ROOT/public/.user.ini-auth-brand-20260720" "${PREFIX}public/.user.ini"
  upload "$ROOT/public/.user.ini-auth-brand-20260720" "${PREFIX}.user.ini"
done

echo "=== verify ==="
curl -sI "https://sportifyplus.de/login" | grep -iE 'x-sportify|HTTP/' || true
curl -sL "https://sportifyplus.de/login" | grep -nE 'auth-brand|20260720-auth-brand' | head -15 || true
curl -sL "https://sportifyplus.de/register" | grep -nE 'auth-brand|20260720-auth-brand' | head -10 || true
