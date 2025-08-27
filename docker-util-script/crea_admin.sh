#!/bin/bash

# Nome del container
CONTAINER_NAME="egonextcloud-nextcloud-1"

sudo docker exec -u www-data -e OC_PASS=Ego.tello1969 -it $CONTAINER_NAME \
  php occ user:add --password-from-env --group admin admin

