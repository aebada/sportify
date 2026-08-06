#!/usr/bin/env bash
# Deploy admin-controlled social login visibility (hide Facebook/LinkedIn by default).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FTP_HOST="${FTP_HOST:-92.113.19.130}"
FTP_USER="${FTP_USER:-u234903558.sportifywebsite}"
FTP_PASS="${FTP_PASS:-}"

REMOTE_ROOTS=(
  "domains/sportifyplus.de/public_html"
  "."
)

FILES=(
  "app/Services/SocialAuthSettings.php"
  "app/Controllers/AdminSocialAuthController.php"
  "app/Controllers/OAuthController.php"
  "app/Controllers/SocialLoginHandler20260621.php"
  "views/partials/social-auth.php"
  "views/partials/social-auth-20260614-linkedin.php"
  "views/admin/social-auth/index.php"
  "views/layouts/admin.php"
  "config/routes.php"
  "lang/en/messages.php"
  "lang/de/messages.php"
  "lang/ar/messages.php"
)

echo "== Upload social auth visibility files =="
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

echo "== Purge OPcache =="
curl -fsS "https://sportifyplus.de/opcache-purge.php" >/dev/null || true

echo "done"
