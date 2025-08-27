#!/bin/bash
set -e

# Prende l'ID del container del servizio "nextcloud" avviato via docker compose
CID=$(docker compose ps -q nextcloud || true)
if [ -z "$CID" ]; then
  echo "âŒ Nessun container 'nextcloud' trovato (docker compose ps -q nextcloud Ã¨ vuoto)"
  exit 1
fi

echo "ðŸ” Verifica stato container..."
docker ps --filter "id=$CID" --format "âž¡ï¸  {{.Names}} ({{.Status}})"

echo "ðŸŒ Verifica accesso via HTTP (http://localhost:8080)..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 | grep -q "^200$"; then
  echo "âœ… Accessibile su http://localhost:8080"
else
  echo "âŒ Non accessibile su http://localhost:8080"
fi

echo "ðŸ“„ Verifica config.php nel container..."
docker compose exec -T nextcloud bash -c 'test -f /var/www/html/config/config.php && echo "âœ… config.php presente" || echo "âŒ config.php mancante"'


echo "ðŸ” Trusted domains:"
if ! docker compose exec -T nextcloud php occ config:system:get trusted_domains; then
  echo "âš ï¸ Errore nella lettura dei trusted domains (container forse non pronto)"
fi
