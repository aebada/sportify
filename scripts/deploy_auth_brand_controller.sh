#!/usr/bin/env bash
set -euo pipefail
export PATH="/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin:$PATH"
HOST="92.113.19.130"
USER="u234903558.sportifywebsite"
PASS="${SPORTIFY_FTP_PASS:?}"
AUTH="${USER}:${PASS}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "$lfile" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}
for BASE in "domains/sportifyplus.de/public_html" "" "public_html"; do
  P="${BASE:+$BASE/}"
  echo "=== ${BASE:-root} ==="
  upload "$ROOT/app/Controllers/AuthController.php" "${P}app/Controllers/AuthController.php"
  upload "$ROOT/views/layouts/auth-v2.php" "${P}views/layouts/auth-v2.php"
  upload "$ROOT/views/layouts/auth.php" "${P}views/layouts/auth.php"
  upload "$ROOT/public/deploy-check.php" "${P}public/deploy-check.php"
  upload "$ROOT/public/deploy-check.php" "${P}deploy-check.php"
done
echo "=== deploy-check probe ==="
curl -sL "https://sportifyplus.de/deploy-check.php" | grep -E '^auth_ctl_|^auth_layout_' || true
echo "=== login verify ==="
curl -sL "https://sportifyplus.de/login?redirect=/discover" | grep -nE 'auth-brand|20260720-auth-brand|name="next"' | head -20 || true
curl -sL "https://sportifyplus.de/login" | grep -nE 'auth-brand|20260720-auth-brand' | head -10 || true
