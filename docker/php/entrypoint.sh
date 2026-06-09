#!/bin/bash
set -e

cd /var/www/html

if [ ! -f "artisan" ]; then
    echo "Creating fresh Laravel project..."
    composer create-project laravel/laravel /tmp/laravel_base --prefer-dist --no-interaction
    cp -rn /tmp/laravel_base/. /var/www/html/
    rm -rf /tmp/laravel_base
fi

echo "Installing PHP dependencies..."
composer install --no-interaction --optimize-autoloader

if [ ! -f ".env" ]; then
    cp .env.example .env
fi

php artisan key:generate --no-interaction --force 2>/dev/null || true

echo "Running migrations..."
php artisan migrate --force --no-interaction

php artisan queue:table 2>/dev/null || true
php artisan migrate --force --no-interaction

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

exec "$@"
