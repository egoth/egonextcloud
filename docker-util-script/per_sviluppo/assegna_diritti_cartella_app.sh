docker compose exec nextcloud bash -lc '
  chown -R www-data:www-data /var/www/html/custom_apps/egonextapp &&
  find /var/www/html/custom_apps/egonextapp -type d -exec chmod 755 {} \; &&
  find /var/www/html/custom_apps/egonextapp -type f -exec chmod 644 {} \;
'

#chown -R www-data:www-data apps



sudo setfacl -R -m u:www-data:rwX apps
sudo setfacl -R -d -m u:www-data:rwX apps
