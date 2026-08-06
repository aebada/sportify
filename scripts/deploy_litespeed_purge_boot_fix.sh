#!/usr/bin/env bash
# Deploy LiteSpeed-Purge removal + OAuth/login boot fixes to all Hostinger FTP roots.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FTP_HOST="${FTP_HOST:-92.113.19.130}"
FTP_USER="${FTP_USER:-u234903558.sportifywebsite}"
FTP_PASS="${FTP_PASS:-}"

if [[ -z "$FTP_PASS" ]]; then
  echo "Set FTP_PASS before running." >&2
  exit 1
fi

REMOTE_ROOTS=(
  "."
  "public_html"
  "domains/sportifyplus.de/public_html"
)

# Project-relative paths to upload (also mirrored for web entrypoints)
FILES=(
  "bootstrap.php"
  "bootstrap-20260621-authfix.php"
  "config/wellness.php"
  "site-prepend-20260705-refereex-live.php"
  "site-prepend-20260707-refereex.php"
  "public/oauth-google-start-fix-20260720h.php"
  "public/oauth-google-start-20260720.php"
  "public/oauth-google-cb-fix-20260720e.php"
  "public/oauth-google-callback-20260720.php"
  "public/login-emergency-20260720g.php"
  "public/site-prepend-20260705-refereex-live.php"
  "public/site-prepend-20260707-refereex.php"
  "public/.htaccess"
)

upload_one() {
  local remote="$1" src="$2" dest="$3"
  lftp -u "$FTP_USER,$FTP_PASS" "$FTP_HOST" -e "
    set ssl:verify-certificate no;
    set net:timeout 45;
    set xfer:clobber on;
    cd /$remote;
    put $src -o $dest;
    bye
  " >/dev/null
  echo "  uploaded /$remote/$dest"
}

echo "== Upload purge-fix + oauth/login boot =="
for remote in "${REMOTE_ROOTS[@]}"; do
  echo "-- root /$remote"
  for rel in "${FILES[@]}"; do
    src="$ROOT/$rel"
    if [[ ! -f "$src" ]]; then
      echo "missing $rel" >&2
      exit 1
    fi
    upload_one "$remote" "$src" "$rel"

    if [[ "$rel" == public/* ]]; then
      base="$(basename "$rel")"
      if [[ "$base" != ".htaccess" ]]; then
        upload_one "$remote" "$src" "$base" || true
      else
        # Docroot public/.htaccess → also as public/.htaccess (already) and as nested if needed
        upload_one "$remote" "$src" "public/.htaccess" || true
      fi
    fi
  done

  # Ensure front-controller .htaccess at public/ for nested layout
  upload_one "$remote" "$ROOT/public/.htaccess" "public/.htaccess" || true
done

echo "== Verify live =="
for path in / /login /auth/google /auth/google/callback; do
  echo "--- $path"
  curl -sI "https://sportifyplus.de$path" | tr -d '\r' | head -20
  echo
done

echo "done"
