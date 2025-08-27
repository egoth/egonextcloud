#!/bin/bash
#!/bin/bash

# Nome del container
CONTAINER_NAME="nextcloud_heic"

echo "➡️ Entrata nel container $CONTAINER_NAME..."

# Esegui i comandi nel container
sudo docker exec -u www-data -it $CONTAINER_NAME bash -c "
  cd /var/www/html && \
  php occ config:system:set enabledPreviewProviders 0 --value=OC\\Preview\\HEIC && \
  php occ config:system:set enabledPreviewProviders 0 --value=OC\\Preview\\Image && \
  php occ config:system:set enabledPreviewProviders 0 --value=OC\\Preview\\JPEG && \
  php occ config:system:set enabledPreviewProviders 0 --value=OC\\Preview\\PNG && \
  php occ app:install previewgenerator && \
  php occ app:enable previewgenerator && \
  php occ preview:generate-all



"

# Riavvia Apache nel container
echo "🔁 Riavvio Apache nel container..."
sudo docker exec -it $CONTAINER_NAME service apache2 restart

