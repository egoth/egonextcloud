#!/usr/bin/env bash
set -euo pipefail

# Config
APP_ID="${APP_ID:-egonextapp}"            # export APP_ID=... per cambiare app
SERVICE="${SERVICE:-nextcloud}"           # nome servizio in docker-compose
DO_PULL="${DO_PULL:-1}"                   # 1 = esegui git pull prima

echo "🔧 Update app: $APP_ID (service: $SERVICE)"

# 0) (opzionale) git pull del repo host
if [[ "$DO_PULL" == "1" ]]; then
  echo "⬇️  git pull..."
  git pull --ff-only
fi

# 1) Disabilita l'app (se già off non fa nulla)
echo "⛔ Disabilito app (se attiva)..."
docker compose exec -T -u www-data "$SERVICE" php occ app:disable "$APP_ID" || true

# 2) Svuota cache di Nextcloud
echo "🧹 Pulizia cache Nextcloud..."
docker compose exec -T -u www-data "$SERVICE" php occ cache:clear || true

# 3) Ricarica Apache (per svuotare opcache PHP)
echo "♻️  Ricarico Apache nel container..."
docker compose exec -T "$SERVICE" bash -lc "apachectl -k graceful" || docker compose restart "$SERVICE"

# 4) Riabilita l'app
echo "✅ Riabilito app..."
docker compose exec -T -u www-data "$SERVICE" php occ app:enable "$APP_ID"

# 5) Verifica stato app
echo "🔎 Verifica stato:"
docker compose exec -T -u www-data "$SERVICE" php occ app:list | grep -E "Enabled:|Disabled:" -A999 | grep -E "$APP_ID|^Enabled:|^Disabled:" || true

echo "🎉 Fatto!"

