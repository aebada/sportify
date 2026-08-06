#!/usr/bin/env bash
# FASTEST sportifyplus.de bypass: overwrite frozen static live-sports.html
# This path returns HTTP 200 and bypasses PHP router + OPcache.
# Run from a network where FTP port 21 is reachable (not blocked).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"
REMOTE="domains/sportifyplus.de/public_html/live-sports.html"
LOCAL="${ROOT}/public/refereex-live-now.html"
MARKER="refereex-live-now-$(date -u +%Y%m%dT%H%M%SZ)"

echo "=== Upload RefereeX → live-sports.html (${MARKER}) ==="
curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
  -u "$AUTH" -T "$LOCAL" "ftp://${HOST}/${REMOTE}"

echo "=== Verify HTTP ==="
sleep 3
curl -sSI "https://sportifyplus.de/live-sports.html" | grep -E 'HTTP/|last-modified|content-type'
curl -sS "https://sportifyplus.de/live-sports.html" | grep -iE 'RefereeX|rx-hero|deploy-marker' | head -5

echo ""
echo "LIVE URL: https://sportifyplus.de/live-sports.html"
