#!/usr/bin/env bash
# Deploy Socialite-equivalent Google OAuth for Sportify Plus to ALL Hostinger FTP roots.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FTP_HOST="${FTP_HOST:-92.113.19.130}"
FTP_USER="${FTP_USER:-u234903558.sportifywebsite}"
FTP_PASS="${FTP_PASS:-}"

if [[ -z "$FTP_PASS" ]]; then
  echo "Set FTP_PASS (Hostinger FTP password) before running." >&2
  exit 1
fi

REMOTE_ROOTS=(
  "."
  "public_html"
  "domains/sportifyplus.de/public_html"
)

FILES=(
  "bootstrap.php"
  "config/config.php"
  "config/services.php"
  "app/Services/DatabaseSetup.php"
  "app/Services/SocialAuthService.php"
  "app/Services/GoogleAuthService.php"
  "app/Services/GoogleOAuthStart.php"
  "app/Models/User.php"
  "app/Models/MemberProfile.php"
  "app/Controllers/OAuthController.php"
  "app/Controllers/SocialLoginHandler20260621.php"
  "app/Controllers/GoogleLoginController.php"
  "app/Core/Controller.php"
  "views/partials/google-auth-button.php"
  "views/partials/social-auth-20260720-visibility.php"
  "public/oauth-google-callback-20260720.php"
  "public/oauth-google-cb-fix-20260720e.php"
  "public/oauth-google-return-20260720.php"
  "public/oauth-google-start-20260720.php"
  "public/oauth-env-diag.php"
  "public/migrate-google-oauth-20260720.php"
  "public/google-oauth-probe-20260720.php"
  "public/.htaccess"
  "database/migrations/20260720_add_google_oauth_columns.sql"
  "docs/google-oauth-hopn.md"
)

DOCROOT_MIRRORS=(
  "public/oauth-google-callback-20260720.php:oauth-google-callback-20260720.php"
  "public/oauth-google-cb-fix-20260720e.php:oauth-google-cb-fix-20260720e.php"
  "public/oauth-google-return-20260720.php:oauth-google-return-20260720.php"
  "public/oauth-google-start-20260720.php:oauth-google-start-20260720.php"
  "public/oauth-env-diag.php:oauth-env-diag.php"
  "public/migrate-google-oauth-20260720.php:migrate-google-oauth-20260720.php"
)

upload_one() {
  local remote="$1" src="$2" dest="$3"
  local dest_dir
  dest_dir="$(dirname "$dest")"
  lftp -u "$FTP_USER,$FTP_PASS" "$FTP_HOST" -e "
    set ssl:verify-certificate no;
    set net:timeout 45;
    set net:max-retries 2;
    set xfer:clobber on;
    cd /$remote;
    $( [[ "$dest_dir" != "." ]] && echo "mkdir -p $dest_dir;" )
    put $src -o $dest;
    bye
  " >/dev/null
  echo "  uploaded /$remote/$dest"
}

echo "== Upload Google OAuth (Socialite-equivalent) =="
for remote in "${REMOTE_ROOTS[@]}"; do
  echo "-- root /$remote"
  for rel in "${FILES[@]}"; do
    src="$ROOT/$rel"
    if [[ ! -f "$src" ]]; then
      echo "missing $rel" >&2
      exit 1
    fi
    upload_one "$remote" "$src" "$rel"
  done
  for pair in "${DOCROOT_MIRRORS[@]}"; do
    src_rel="${pair%%:*}"
    dest="${pair##*:}"
    upload_one "$remote" "$ROOT/$src_rel" "$dest"
  done
  # Docroot .htaccess (account/domain layout uses nested public/)
  if [[ -f "$ROOT/public/.htaccess-docroot-oauth-google-20260720f" ]]; then
    upload_one "$remote" "$ROOT/public/.htaccess-docroot-oauth-google-20260720f" ".htaccess"
  fi
done

if [[ -n "${GOOGLE_CLIENT_ID:-}" && -n "${GOOGLE_CLIENT_SECRET:-}" ]]; then
  echo "== Patch production .env Google credentials =="
  php "$ROOT/scripts/patch_production_google_env.php" \
    --host="$FTP_HOST" \
    --user="$FTP_USER" \
    --pass="$FTP_PASS" \
    --client-id="$GOOGLE_CLIENT_ID" \
    --client-secret="$GOOGLE_CLIENT_SECRET" \
    --redirect-uri="${GOOGLE_REDIRECT_URI:-https://sportifyplus.de/auth/google/callback}"
else
  echo "== Skip .env credential patch (credentials already on server or set GOOGLE_CLIENT_ID/SECRET) =="
fi

echo "== Purge + verify =="
curl -fsS "https://sportifyplus.de/opcache-purge.php?t=$(date +%s)" >/dev/null || true
curl -fsS "https://sportifyplus.de/oauth-env-diag.php?t=$(date +%s)" | head -40 || true
echo
curl -fsS "https://sportifyplus.de/migrate-google-oauth-20260720.php?t=$(date +%s)" || true
echo
echo "== Verify start / callback =="
curl -sI "https://sportifyplus.de/auth/google" | tr -d '\r' | head -15
echo
curl -sI "https://sportifyplus.de/auth/google/callback" | tr -d '\r' | head -15
echo
echo "done"
