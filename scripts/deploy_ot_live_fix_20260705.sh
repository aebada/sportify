#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${FTP_HOST:-92.113.19.130}"
USER="${FTP_USER:-u234903558.sportifywebsite}"
PASS="${FTP_PASS:-}"
AUTH="${USER}:${PASS}"

upload() {
  local lfile="$1" remote="$2"
  printf '%s ... ' "$remote"
  curl -sS --connect-timeout 30 --max-time 180 --ftp-pasv --ftp-create-dirs \
    -u "$AUTH" -T "${ROOT}/${lfile}" "ftp://${HOST}/${remote}" && echo OK || echo FAIL
}

echo "=== OnlyTalents live fix 20260705 ==="

# 5 FTP roots for talents gateway (no PHP auto_prepend — Apache redirect only)
TALENT_BASES=(
  "public_html"
  "domains/sportifyplus.de/public_html/talents"
  "talents"
  "domains/talents.sportifyplus.de/public_html"
)

GATEWAY_FILES=(
  "public/index-onlytalents.html:index.html"
  "public/index-onlytalents-redirect.php:index.php"
  "public/talents-live-fix-20260705/.htaccess:.htaccess"
  "public/.user.ini-talents-minimal:.user.ini"
  "public/deploy-marker-20260705.txt:deploy-marker-20260705.txt"
)

for base in "${TALENT_BASES[@]}"; do
  echo "--- $base ---"
  for pair in "${GATEWAY_FILES[@]}"; do
    upload "${pair%%:*}" "${base}/${pair#*:}"
  done
done

# Account root .htaccess — remove AddHandler php82-on-all-files; keep OT subdomain 302
upload "public/.htaccess-account-root-merged" "public_html/.htaccess"

# Main site: prepend /talents redirect into root .htaccess
MAIN_HT="${ROOT}/public/.htaccess-main-live-20260705"
cp "${ROOT}/public/.htaccess-account-root-merged" "$MAIN_HT"
# Replace main-site block with domains/sportifyplus.de style + /talents redirect
cat > "$MAIN_HT" << 'HTEOF'
# Root .htaccess for Hostinger shared hosting.
# Document root is the project folder; all web traffic is forwarded into /public.

<IfModule mod_rewrite.c>
    RewriteEngine On

    # Legacy /talents folder → OnlyTalents
    RewriteRule ^talents/?$ https://sportifyplus.de/only-talents/home [R=302,L]

    # Canonical host — apex only
    RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]
    RewriteRule ^ https://%1%{REQUEST_URI} [R=301,L]

    # Canonical URLs — strip legacy /public/ prefix
    RewriteCond %{REQUEST_URI} ^/public/?(.*)$ [NC]
    RewriteRule ^ https://%{HTTP_HOST}/%1 [R=301,L]

    # Block direct access to application internals
    RewriteRule ^(app|config|database|storage|views|bootstrap\.php)(/|$) - [F,L]
    RewriteRule ^lang/(en|de|ar)/messages\.php$ - [F,L]

    # RefereeX AI — map pretty URL to existing CDN-known static entry (OPcache bypass)
    RewriteRule ^refereex-ai/?$ public/deploy-marker.txt [L]

    # Homepage → front controller directly (avoids directory 403)
    RewriteRule ^$ public/index.php [L]

    # Forward all other requests into the public folder
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.+)$ public/$1 [L]
</IfModule>

Options -Indexes
<FilesMatch "\.(env|sqlite|md)$">
    Require all denied
</FilesMatch>

<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
</IfModule>

<IfModule mod_headers.c>
    <FilesMatch "\.(css|js|png|jpe?g|gif|webp|svg|ico|woff2?)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript application/json image/svg+xml
</IfModule>
HTEOF

upload "$MAIN_HT" "domains/sportifyplus.de/public_html/.htaccess"

# Site prepends — OT subdomain early 302 before any bootstrap
upload "public/site-prepend-20260705-ot.php" "domains/sportifyplus.de/public_html/site-prepend-20260705-ot.php"
upload "public/site-prepend-20260705-ot.php" "domains/sportifyplus.de/public_html/talents/site-prepend-20260705-ot.php"
upload "public/site-prepend-20260705-refereex-live.php" "public_html/public/site-prepend-20260705-refereex-live.php"

# Main site .user.ini — OT prepend at project root
upload "public/.user.ini-production" "domains/sportifyplus.de/public_html/.user.ini"

echo "=== opcache purge ==="
curl -s 'https://sportifyplus.de/opcache-purge.php' | head -3 || true
curl -s 'https://sportifyplus.de/public/opcache-purge.php' | head -3 || true

echo "=== verify ==="
for u in \
  'https://sportifyplus.de/only-talents/home' \
  'https://sportifyplus.de/only-talents/discover' \
  'https://sportifyplus.de/talents' \
  'https://sportifyplus.de/talents/' \
  'https://talents.sportifyplus.de/' \
  'https://talents.sportifyplus.de/discover'; do
  echo "$u"
  curl -sI "$u" | head -6
  echo
done
