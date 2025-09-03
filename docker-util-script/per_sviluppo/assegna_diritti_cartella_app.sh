docker compose exec nextcloud bash -lc '
  chown -R www-data:www-data /var/www/html/custom_apps/egonextapp &&
  find /var/www/html/custom_apps/egonextapp -type d -exec chmod 755 {} \; &&
  find /var/www/html/custom_apps/egonextapp -type f -exec chmod 644 {} \;
'
