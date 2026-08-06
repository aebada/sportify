#!/usr/bin/env bash
# Deploy AI Pass-aligned Google OAuth to Sportify production (Hostinger FTP).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FTP_HOST="${FTP_HOST:-92.113.19.130}"
FTP_USER="${FTP_USER:-u234903558.sportifywebsite}"
FTP_PASS="${FTP_PASS:-}"

# AI Pass Google OAuth (project aipass-501004) — read from AI Pass auth-api .env when present.
AIPASS_ENV="${AIPASS_ENV:-/Volumes/All/Dev/AI-Pass/services/auth-api/.env}"
if [[ -z "${GOOGLE_CLIENT_ID:-}" && -f "$AIPASS_ENV" ]]; then
  GOOGLE_CLIENT_ID="$(grep -E '^GOOGLE_CLIENT_ID=' "$AIPASS_ENV" | head -1 | cut -d= -f2- | tr -d '"')"
  GOOGLE_CLIENT_SECRET="$(grep -E '^GOOGLE_CLIENT_SECRET=' "$AIPASS_ENV" | head -1 | cut -d= -f2- | tr -d '"')"
fi
if [[ -z "${GOOGLE_CLIENT_ID:-}" || -z "${GOOGLE_CLIENT_SECRET:-}" ]]; then
  echo "Set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET (or ensure $AIPASS_ENV exists)" >&2
  exit 1
fi

REMOTE_ROOTS=(
  "domains/sportifyplus.de/public_html"
)

FILES=(
  "app/Services/SocialAuthService.php"
  "app/Controllers/OAuthController.php"
  "app/Controllers/SocialLoginHandler20260621.php"
  "app/Models/User.php"
  "app/Models/MemberProfile.php"
  "public/deploy-check.php"
  "public/oauth-env-diag.php"
)

echo "== Upload OAuth files =="
for remote in "${REMOTE_ROOTS[@]}"; do
  for rel in "${FILES[@]}"; do
    src="$ROOT/$rel"
    [[ -f "$src" ]] || { echo "missing local $rel" >&2; exit 1; }
    lftp -u "$FTP_USER,$FTP_PASS" "$FTP_HOST" -e "
      set ssl:verify-certificate no;
      set xfer:clobber on;
      cd /$remote;
      put $src -o $rel;
      bye
    " >/dev/null
    echo "  uploaded /$remote/$rel"
  done
done

echo "== Patch production .env Google credentials =="
php "$ROOT/scripts/patch_production_google_env.php" \
  --host="$FTP_HOST" \
  --user="$FTP_USER" \
  --pass="$FTP_PASS" \
  --client-id="$GOOGLE_CLIENT_ID" \
  --client-secret="$GOOGLE_CLIENT_SECRET"

echo "== Purge OPcache =="
curl -fsS "https://sportifyplus.de/opcache-purge.php" >/dev/null || true
curl -fsS "https://sportifyplus.de/oauth-env-diag.php" | rg -v 'SECRET|PASSWORD' || true

echo "done"
