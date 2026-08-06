#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"
BASE="public_html"

upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  if curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${lfile}" "ftp://${HOST}/${remote}" >/dev/null; then
    echo OK
  else
    echo FAIL
    return 1
  fi
}

echo "=== RefereeX i18n deploy $(date -u +%Y%m%dT%H%M%SZ) ==="
upload "app/Core/Lang.php" "${BASE}/app/Core/Lang.php"
upload "lang/en/refereex-messages.php" "${BASE}/lang/en/refereex-messages.php"
upload "lang/de/refereex-messages.php" "${BASE}/lang/de/refereex-messages.php"
upload "lang/ar/refereex-messages.php" "${BASE}/lang/ar/refereex-messages.php"
echo "=== Done ==="
