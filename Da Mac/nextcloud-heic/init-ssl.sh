#!/bin/bash
set -e

# Installa nano (solo se non già installato)
apt update && apt install -y nano && apt clean

# Inserisce i trusted domains automaticamente
#sed -i '/trusted_domains/,+5d' /var/www/html/config/config.php
#sed -i '/);/i \  '\''trusted_domains'\'' => array ( 0 => '\''localhost'\'', 1 => '\''192.168.1.188'\'', 2 => '\''egodom.hopto.org'\'' ),' /var/www/html/config/config.php

# Abilita modulo SSL
a2enmod ssl

# Corregge il file se è reale invece di symlink
if [ -f /etc/apache2/sites-enabled/default-ssl.conf ]; then
    rm /etc/apache2/sites-enabled/default-ssl.conf
fi
a2ensite default-ssl

# Configura certificati SSL
SSL_CONF="/etc/apache2/sites-available/default-ssl.conf"
sed -i 's|SSLCertificateFile.*|SSLCertificateFile /etc/apache2/ssl/fullchain.pem|' "$SSL_CONF"
sed -i 's|SSLCertificateKeyFile.*|SSLCertificateKeyFile /etc/apache2/ssl/privkey.pem|' "$SSL_CONF"

# Continua con il normale avvio
exec "$@"

