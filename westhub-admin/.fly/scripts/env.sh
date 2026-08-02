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

# Re-create symlink at runtime to ensure it survives volume mounts
rm -rf /var/www/html/public/storage
ln -s /var/www/html/storage/app/public /var/www/html/public/storage

# Run migrations and seed if volume is empty (initial setup)
if [ ! -d "/var/www/html/storage/app/public/1" ]; then
    echo "First time setup: Seeding database..."
    php /var/www/html/artisan db:seed --force
fi

