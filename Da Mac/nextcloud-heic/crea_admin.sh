#!/bin/bash
sudo docker exec -u www-data -e OC_PASS=Ego.tello1969 -it nextcloud_heic \
  php occ user:add --password-from-env --group admin admin

