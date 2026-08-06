#!/usr/bin/env bash
# Deploy RefereeX page to GitHub Pages (external fallback when Hostinger is frozen)
# Usage: bash scripts/deploy_refereex_external_ghpages.sh
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
REPO="https://github.com/aebada/sportify-refereex.git"
WORKDIR="$(mktemp -d)"
trap 'rm -rf "$WORKDIR"' EXIT

cp "${ROOT}/public/refereex-live-now.html" "${WORKDIR}/index.html"
cd "$WORKDIR"
git init -q
git checkout -b main
git add index.html
git commit -q -m "Deploy RefereeX AI landing page for Sportify"
git remote add origin "$REPO"
git push -u origin main --force

gh api "repos/aebada/sportify-refereex/pages" -X POST \
  -f source[branch]=main -f source[path]=/ 2>/dev/null || true

echo ""
echo "=== LIVE URL (wait 1-2 min for Pages build) ==="
echo "https://aebada.github.io/sportify-refereex/"
echo ""
curl -sSI "https://aebada.github.io/sportify-refereex/" | head -3 || true
