#!/bin/bash

# Nome del container
CONTAINER_NAME="egonextcloud-nextcloud-1"

sudo docker exec -u www-data -e OC_PASS=Gyps0l0ne1 -it $CONTAINER_NAME \
  php occ user:add --password-from-env --group users consuelo
sudo docker exec -u www-data -e OC_PASS=Gyps0l0ne1 -it $CONTAINER_NAME \
  php occ user:add --password-from-env --group users lucrezia
sudo docker exec -u www-data -e OC_PASS=Gyps0l0ne1 -it $CONTAINER_NAME \
  php occ user:add --password-from-env --group users leonardo
