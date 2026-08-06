#!/usr/bin/env bash
# Deploy Google OAuth callback fix to all Hostinger FTP roots.
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

FILES=(
  "bootstrap.php"
  "app/Services/SocialAuthService.php"
  "app/Services/GoogleOAuthStart.php"
  "app/Controllers/OAuthController.php"
  "app/Controllers/SocialLoginHandler20260621.php"
  "public/oauth-google-callback-20260720.php"
  "public/oauth-google-cb-fix-20260720e.php"
  "public/oauth-google-return-20260720.php"
  "public/oauth-google-start-20260720.php"
  "public/oauth-env-diag.php"
  "public/.htaccess"
)

echo "== Upload OAuth callback fix =="
for remote in "${REMOTE_ROOTS[@]}"; do
  echo "-- root /$remote"
  for rel in "${FILES[@]}"; do
    src="$ROOT/$rel"
    if [[ ! -f "$src" ]]; then
      echo "missing $rel" >&2
      exit 1
    fi
    # public_html may be a docroot without nested public/ — also mirror web files at root
    lftp -u "$FTP_USER,$FTP_PASS" "$FTP_HOST" -e "
      set ssl:verify-certificate no;
      set net:timeout 30;
      set xfer:clobber on;
      cd /$remote;
      put $src -o $rel;
      bye
    " >/dev/null
    echo "  uploaded /$remote/$rel"

    # Mirror web entrypoints into public/ and (for docroots) as top-level copies,
    # but NEVER overwrite the project-root .htaccess with public/.htaccess.
    if [[ "$rel" == public/* ]]; then
      base="$(basename "$rel")"
      if [[ "$base" != ".htaccess" ]]; then
        lftp -u "$FTP_USER,$FTP_PASS" "$FTP_HOST" -e "
          set ssl:verify-certificate no;
          set net:timeout 30;
          set xfer:clobber on;
          cd /$remote;
          put $src -o $base;
          bye
        " >/dev/null || true
        echo "  uploaded /$remote/$base"
      fi
    fi
  done
done

echo "== Purge OPcache =="
curl -fsS "https://sportifyplus.de/oauth-env-diag.php?t=$(date +%s)" | head -40 || true
echo
echo "== Verify callback (no code) =="
curl -sI "https://sportifyplus.de/auth/google/callback" | tr -d '\r' | head -20
echo
curl -sI "https://sportifyplus.de/oauth-google-cb-fix-20260720e.php" | tr -d '\r' | head -20
echo
echo "done"
