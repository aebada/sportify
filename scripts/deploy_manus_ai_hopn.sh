#!/usr/bin/env bash
# Deploy Manus AI (HOPn) client + chatbot/wellness wiring to Hostinger FTP roots.
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
  "config/config.php"
  "config/ai_analysis.php"
  "app/Services/ManusAiService.php"
  "app/Services/ChatbotService.php"
  "app/Services/WellnessAiService.php"
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

echo "== Upload Manus AI integration =="
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
done

if [[ -n "${MANUS_API_KEY:-}" ]]; then
  echo "== Patch production .env Manus credentials =="
  php "$ROOT/scripts/patch_production_manus_env.php" \
    --host="$FTP_HOST" \
    --user="$FTP_USER" \
    --pass="$FTP_PASS" \
    --api-key="$MANUS_API_KEY" \
    --base-url="${MANUS_BASE_URL:-https://api.manus.ai/v2}" \
    --agent-profile="${MANUS_AGENT_PROFILE:-manus-1.6-lite}" \
    --poll-timeout="${MANUS_POLL_TIMEOUT:-45}" \
    --poll-interval="${MANUS_POLL_INTERVAL:-2}"
else
  echo "== Skip .env Manus patch (set MANUS_API_KEY to upsert production .env) =="
fi

echo "== Purge OPcache =="
curl -fsS "https://sportifyplus.de/opcache-purge.php?t=$(date +%s)" >/dev/null || true
echo "done"
