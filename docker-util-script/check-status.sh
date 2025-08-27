#!/bin/bash

echo "🔍 Verifica stato container..."
docker ps --filter name=egonextcloud-nextcloud --format "➡️  {{.Names}} ({{.Status}})"

echo "🌐 Verifica accesso via HTTPS (https://localhost:8443)..."
curl -k --silent --head https://localhost:8443 | grep "200 OK" && echo "✅ Accessibile" || echo "❌ Non accessibile"

echo "📄 Verifica config.php nel container..."
docker exec egonextcloud-nextcloud bash -c 'test -f /var/www/html/config/config.php && echo "✅ config.php presente" || echo "❌ config.php mancante"'

echo "🔐 Trusted domains:"
docker exec egonextcloud-nextcloud php occ config:system:get trusted_domains || echo "⚠️ Errore nella lettura dei trusted domains"

