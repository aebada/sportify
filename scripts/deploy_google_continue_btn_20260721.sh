#!/usr/bin/env bash
# Deploy Continue-with-Google button (lang + partials + CSS + auth layouts) to all FTP roots.
# Usage: FTP_PASS='...' ./scripts/deploy_google_continue_btn_20260721.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-${SPORTIFY_FTP_PASS:-}}"

if [[ -z "$PASS" ]]; then
  echo "Set FTP_PASS (or SPORTIFY_FTP_PASS) before running." >&2
  exit 1
fi

AUTH="${USER}:${PASS}"
OK=0
FAIL=0

upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  if curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "$lfile" "ftp://${HOST}/${remote}" >/dev/null; then
    echo OK
    OK=$((OK + 1))
  else
    echo FAIL
    FAIL=$((FAIL + 1))
  fi
}

FILES=(
  "bootstrap.php"
  "app/Core/Lang.php"
  "app/Controllers/AuthController.php"
  "lang/en/messages.php"
  "lang/de/messages.php"
  "lang/ar/messages.php"
  "views/partials/google-auth-button.php"
  "views/partials/social-auth.php"
  "views/partials/social-auth-20260720-visibility.php"
  "views/partials/social-auth-20260614-linkedin.php"
  "views/auth/login.php"
  "views/auth/register.php"
  "views/layouts/auth.php"
  "views/layouts/auth-v2.php"
  "public/assets/css/app.css"
  "public/push-google-continue-btn-20260721.php"
  "public/opcache-purge-google-btn-20260721.php"
)

ROOTS=("." "public_html" "domains/sportifyplus.de/public_html")

for BASE in "${ROOTS[@]}"; do
  if [[ "$BASE" == "." ]]; then
    P=""
  else
    P="${BASE}/"
  fi
  echo "======== ${BASE} ========"
  for rel in "${FILES[@]}"; do
    upload "$ROOT/$rel" "${P}${rel}"
  done
  # Also place public helpers at docroot for easy URL hit
  upload "$ROOT/public/push-google-continue-btn-20260721.php" "${P}push-google-continue-btn-20260721.php"
  upload "$ROOT/public/opcache-purge-google-btn-20260721.php" "${P}opcache-purge-google-btn-20260721.php"
  upload "$ROOT/public/push-google-continue-btn-20260721.php" "${P}public/push-google-continue-btn-20260721.php"
  upload "$ROOT/public/opcache-purge-google-btn-20260721.php" "${P}public/opcache-purge-google-btn-20260721.php"
done

echo "DONE OK=$OK FAIL=$FAIL"
exit $([[ "$FAIL" -eq 0 ]] && echo 0 || echo 1)
