#!/bin/bash
#!/bin/bash

# Nome del container
CONTAINER_NAME="nextcloud"

echo "➡️ Entrata nel container $CONTAINER_NAME..."

# Esegui i comandi nel container
sudo docker exec -u www-data -it $CONTAINER_NAME bash -c "
  cd /var/www/html && \
  php occ config:system:set trusted_domains 0 --value=localhost && \
  php occ config:system:set trusted_domains 1 --value=192.168.1.151 && \
  php occ config:system:set trusted_domains 2 --value=egodom.hopto.org
"

# Riavvia Apache nel container
echo "🔁 Riavvio Apache nel container..."
sudo docker exec -it $CONTAINER_NAME service apache2 restart

