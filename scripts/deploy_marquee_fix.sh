#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="${ROOT}/.tmp-marquee-fix"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"
BASE="public_html"

upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${SRC}/${lfile}" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}

echo "=== Scores marquee duplicate fix deploy ==="
upload "views/partials/scores-ticker.php" "${BASE}/views/partials/scores-ticker.php"
upload "public/assets/js/app.js" "${BASE}/assets/js/app.js"
upload "public/assets/js/app.js" "${BASE}/public/assets/js/app.js"
upload "public/assets/js/app.min.prod.js" "${BASE}/assets/js/app.min.js"
upload "public/assets/js/app.min.prod.js" "${BASE}/public/assets/js/app.min.js"

echo "=== HTTP verify ==="
sleep 2
curl -sS 'https://sportifyplus.de/api/scores-ticker?live=1' | python3 -c "import sys,json; d=json.load(sys.stdin); print('api_items=', len(d.get('items',[])))"
curl -sS 'https://sportifyplus.de/' | rg -n 'scores-ticker--static|array_merge' | head -5 || true
curl -sS 'https://sportifyplus.de/assets/js/app.js' | rg -n 'scores-ticker--static|displayItems' | head -5 || true
