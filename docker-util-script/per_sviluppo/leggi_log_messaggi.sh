docker compose exec -T nextcloud sh -lc '
f=/var/www/html/data/nextcloud.log
[ -f "$f" ] || { echo "Log non trovato: $f" >&2; exit 1; }
line=$(grep -n "File creato" "$f" | tail -n 1 | cut -d: -f1)
[ -n "$line" ] || { echo "\"File creato\" non trovato nel log" >&2; exit 2; }
tail -n +$line "$f" | head -n 31
' | jq -r '.message'
