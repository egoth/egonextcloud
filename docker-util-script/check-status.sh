#!/bin/bash
set -euo pipefail

echo "=== Verifica stato container ==="
CID=$(docker compose ps -q nextcloud || true)

if [ -n "$CID" ]; then
  docker ps --filter "id=$CID" --format "Container: {{.Names}} ({{.Status}})"
else
  echo "Container 'nextcloud' non trovato"
fi

echo
echo "=== Verifica accesso via HTTP (http://localhost:8080) ==="
CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 || true)
if [ "$CODE" = "200" ] || [ "$CODE" = "302" ]; then
  echo "HTTP $CODE - Accessibile"
else
  echo "HTTP $CODE - Non accessibile"
fi

if [ -n "$CID" ]; then
  echo
  echo "=== Verifica config.php nel container ==="
  if docker compose exec -T nextcloud bash -lc 'test -f /var/www/html/config/config.php'; then
    echo "config.php presente"
  else
    echo "config.php mancante"
  fi

  echo
  echo "=== Trusted domains ==="
  if ! docker compose exec -T nextcloud php occ config:system:get trusted_domains; then
    echo "Errore nella lettura dei trusted domains (container forse non pronto)"
  fi
fi
