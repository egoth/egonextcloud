#!/bin/bash
set -euo pipefail

# USO:
#   ./docker-util-script/set-trusted-domains.sh [dominio_o_ip ...]
#
# Se non passi argomenti, usa questi default:
DEFAULT_DOMAINS=("localhost" "192.168.1.151" "egodom.hopto.org")

echo "=== Impostazione trusted_domains (docker compose) ==="

# Trova l'ID del container del servizio "nextcloud"
CID="$(docker compose ps -q nextcloud || true)"
if [ -z "$CID" ]; then
  echo "ERRORE: container 'nextcloud' non trovato (docker compose ps -q nextcloud vuoto)."
  exit 1
fi

# Funzione helper per eseguire occ nel container
occ() {
  docker compose exec -T nextcloud php occ "$@"
}

# Attendi che occ sia disponibile
echo "Attendo che Nextcloud sia pronto (occ status)..."
for i in $(seq 1 60); do
  if occ status >/dev/null 2>&1; then
    break
  fi
  sleep 1
  if [ "$i" -eq 60 ]; then
    echo "ERRORE: occ non disponibile (container non pronto)."
    exit 1
  fi
done
echo "Nextcloud OK."

# Domini da impostare
if [ "$#" -gt 0 ]; then
  DOMAINS=("$@")
else
  DOMAINS=("${DEFAULT_DOMAINS[@]}")
fi

echo
echo "Domini da impostare (in ordine):"
idx=0
for d in "${DOMAINS[@]}"; do
  echo "  [$idx] $d"
  idx=$((idx+1))
done

echo
echo "Imposto trusted_domains..."
# Nota: settiamo in modo idempotente, sovrascrivendo gli indici 0..N-1
# e rimuovendo eventuali indici oltre la lista data.

# Prima leggiamo quanti ce ne sono adesso
CURRENT_COUNT=$(occ config:system:get trusted_domains 2>/dev/null | wc -l || true)
[ -z "$CURRENT_COUNT" ] && CURRENT_COUNT=0

# Imposta/aggiorna ciascun dominio al proprio indice
i=0
for dom in "${DOMAINS[@]}"; do
  # normalizza rimuovendo schema e path
  dom="${dom#http://}"; dom="${dom#https://}"; dom="${dom%%/*}"
  echo "  -> trusted_domains[$i] = $dom"
  occ config:system:set trusted_domains "$i" --value "$dom" >/dev/null
  i=$((i+1))
done

# Rimuovi eventuali voci in eccesso (se ce ne sono)
if [ "$CURRENT_COUNT" -gt "${#DOMAINS[@]}" ]; then
  echo "Rimuovo domini extra dagli indici ${#DOMAINS[@]}..$((CURRENT_COUNT-1))"
  for j in $(seq "${#DOMAINS[@]}" $((CURRENT_COUNT-1))); do
    # non esiste "unset" diretto; sovrascriviamo tutti con i nuovi e poi
    # opzionalmente si potrebbe rigenerare config.php. Per semplicitÃ , informiamo solo.
    echo "  (Nota) trusted_domains[$j] extra: rimuovilo a mano se necessario"
  done
fi

echo
echo "Trusted domains attuali:"
occ config:system:get trusted_domains || true

echo
echo "Reload Apache nel container..."
docker compose exec -T nextcloud bash -lc 'apachectl -k graceful || service apache2 reload || service apache2 restart' || true

echo "Fatto."
