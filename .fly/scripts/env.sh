#!/usr/bin/env bash

# If prod.env exists, copy it to .env
if [ -f "/var/www/html/prod.env" ]; then
    echo "Loading production environment variables from prod.env..."
    cp /var/www/html/prod.env /var/www/html/.env
fi

# Ensure storage permissions are correct for the volume
echo "Fixing storage permissions..."
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
