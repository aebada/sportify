#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"
MARKER="refereex-ghpages-redirect-$(date -u +%Y%m%dT%H%M%SZ)"

upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${lfile}" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}

echo "=== RefereeX GitHub Pages redirect deploy (${MARKER}) ==="

# 1. External redirect .htaccess at account root paths
upload ".deploy-artifacts/htaccess-refereex-ghpages-redirect-20260715" "domains/sportifyplus.de/public_html/.htaccess"
upload ".deploy-artifacts/htaccess-refereex-ghpages-redirect-20260715" "public_html/.htaccess"

# 2. RefereeX HTML → live-sports.html (all known static paths)
for remote in \
  "domains/sportifyplus.de/public_html/live-sports.html" \
  "domains/sportifyplus.de/public_html/public/live-sports.html" \
  "public_html/live-sports.html" \
  "public_html/public/live-sports.html"; do
  upload "public/refereex-live-now.html" "$remote"
done

echo "=== HTTP verify ==="
sleep 4
for u in \
  'https://sportifyplus.de/refereex-ai' \
  'https://sportifyplus.de/live-sports.html' \
  'https://sportifyplus.de/fit-pass?view=refereex'; do
  echo "--- $u ---"
  curl -sSI "$u" | grep -E 'HTTP/|location:|last-modified|content-type' || true
done
