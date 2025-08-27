#!/bin/bash
sudo docker exec -u www-data -e OC_PASS=Gyps0l0ne1 -it nextcloud \
  php occ user:add --password-from-env --group users consuelo
sudo docker exec -u www-data -e OC_PASS=Gyps0l0ne1 -it nextcloud \
  php occ user:add --password-from-env --group users lucrezia
sudo docker exec -u www-data -e OC_PASS=Gyps0l0ne1 -it nextcloud \
  php occ user:add --password-from-env --group users leonardo
