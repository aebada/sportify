#!/usr/bin/env bash
# Deploy admin social-auth toggle (routes, controller, view, nav, lang).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"

REMOTE_ROOTS=(
  "domains/sportifyplus.de/public_html"
  "."
)

FILES=(
  "app/Services/SocialAuthSettings.php"
  "app/Controllers/AdminSocialAuthController.php"
  "views/admin/social-auth/index.php"
  "views/layouts/admin.php"
  "config/routes.php"
  "lang/en/messages.php"
  "lang/de/messages.php"
  "lang/ar/messages.php"
  "public/opcache-purge-social-auth-visibility-20260720.php"
)

upload() {
  local local="$1" remote="$2"
  printf '  %s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${local}" "ftp://${HOST}/${remote}" && echo OK || { echo FAIL; return 1; }
}

echo "== Upload admin social-auth files =="
for base in "${REMOTE_ROOTS[@]}"; do
  echo "-- /${base} --"
  for rel in "${FILES[@]}"; do
    [[ -f "${ROOT}/${rel}" ]] || { echo "missing local ${rel}" >&2; exit 1; }
    upload "$rel" "${base}/${rel}"
  done
done

echo "== Purge OPcache =="
curl -fsS --max-time 30 "https://sportifyplus.de/opcache-purge-social-auth-visibility-20260720.php" || \
  curl -fsS --max-time 30 "https://sportifyplus.de/opcache-purge.php" || true

echo "done"
