#!/usr/bin/env sh
set -eu

cd /var/www/html

mkdir -p \
    bootstrap/cache \
    database \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    .docker-runtime

touch database/database.sqlite

if [ -z "${APP_KEY:-}" ]; then
    key_file=".docker-runtime/app_key"

    if [ ! -s "$key_file" ]; then
        php -r 'echo "base64:".base64_encode(random_bytes(32));' > "$key_file"
        chmod 600 "$key_file"
    fi

    APP_KEY="$(cat "$key_file")"
    export APP_KEY
fi

chown -R www-data:www-data bootstrap/cache database storage .docker-runtime

php artisan migrate --force --no-interaction

if [ "${RUN_SEEDER:-true}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

php artisan config:cache --no-interaction
php artisan view:cache --no-interaction

exec "$@"
