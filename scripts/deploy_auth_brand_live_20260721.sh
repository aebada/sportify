#!/usr/bin/env bash
# Deploy auth-brand logo + Google OAuth priority files to all Hostinger FTP roots.
# Usage: FTP_PASS='...' ./scripts/deploy_auth_brand_live_20260721.sh
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
ROOT_FILTER="${1:-}" # optional: . | public_html | domains/sportifyplus.de/public_html

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

CORE=(
  "views/layouts/auth.php"
  "views/layouts/auth-v2.php"
  "app/Controllers/AuthController.php"
  "app/Controllers/OAuthController.php"
  "app/Services/SocialAuthService.php"
  "app/Services/GoogleAuthService.php"
  "app/Services/GoogleOAuthStart.php"
  "config/config.php"
  "config/services.php"
)

PUBLIC=(
  "public/oauth-google-start-20260720.php"
  "public/oauth-google-start-20260720g.php"
  "public/oauth-google-start-fix-20260720g.php"
  "public/oauth-google-start-fix-20260720h.php"
  "public/oauth-google-callback-20260720.php"
  "public/oauth-google-cb-20260720g.php"
  "public/oauth-google-cb-fix-20260720e.php"
  "public/oauth-google-return-20260720.php"
  "public/oauth-google-return-20260720g.php"
  "public/oauth-google-diag-20260720.php"
  "public/oauth-env-diag.php"
  "public/migrate-google-oauth-20260720.php"
  "public/deploy-check.php"
  "public/opcache-purge.php"
)

ROOTS=("." "public_html" "domains/sportifyplus.de/public_html")
if [[ -n "$ROOT_FILTER" ]]; then
  ROOTS=("$ROOT_FILTER")
fi

for BASE in "${ROOTS[@]}"; do
  if [[ "$BASE" == "." ]]; then
    P=""
  else
    P="${BASE}/"
  fi
  echo "======== ${BASE} ========"
  for rel in "${CORE[@]}"; do
    upload "$ROOT/$rel" "${P}${rel}"
  done
  for rel in "${PUBLIC[@]}"; do
    upload "$ROOT/$rel" "${P}${rel}"
    base="$(basename "$rel")"
    upload "$ROOT/$rel" "${P}${base}"
  done
done

echo "DONE OK=$OK FAIL=$FAIL"
exit $([[ "$FAIL" -eq 0 ]] && echo 0 || echo 1)
