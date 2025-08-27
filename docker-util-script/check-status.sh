#!/bin/bash
set -euo pipefail

# ID del container del servizio "nextcloud" (docker compose)
CID=$(docker compose ps -q nextcloud || true)

echo "ðŸ” Verifica stato container..."
if [ -n "$CID" ]; then
  docker ps --filter "id=$CID" --format "âž¡ï¸  {{.Names}} ({{.Status}})"
else
  echo "âŒ Container 'nextcloud' non trovato (docker compose ps -q nextcloud Ã¨ vuoto)"
fi

echo "ðŸŒ Verifica accesso via HTTP (http://localhost:8080)..."
CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 || true)
if [ "$CODE" = "200" ] || [ "$CODE" = "302" ]; then
  echo "âœ… Accessibile (HTTP $CODE)"
else
  echo "âŒ Non accessibile (HTTP $CODE)"
fi

if [ -n "$CID" ]; then
  echo "ðŸ“„ Verifica config.php nel container..."
  if docker compose exec -T nextcloud bash -lc 'test -f /var/www/html/config/config.php'; then
    echo "âœ… config.php presente"
  else
    echo "âŒ config.php mancante"
  fi

  echo "ðŸ” Trusted domains:"
  if ! docker compose exec -T nextcloud php occ config:system:get trusted_domains; then
    echo "âš ï¸ Errore nella lettura dei trusted domains (container forse non pronto)"
  fi
fi
