# cancella cache di Nextcloud
docker compose exec -u www-data nextcloud php occ cache:clear

# ricarica Apache (svuota opcache)
docker compose exec nextcloud apachectl -k graceful
