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

if [ -z "${APP_KEY:-}" ]; then
    key_file=".docker-runtime/app_key"

    if [ ! -s "$key_file" ]; then
        php -r 'echo "base64:".base64_encode(random_bytes(32));' > "$key_file"
        chmod 600 "$key_file"
    fi

    APP_KEY="$(cat "$key_file")"
    export APP_KEY
fi

chown -R www-data:www-data bootstrap/cache storage .docker-runtime

attempt=1
until mysqladmin ping \
    --host="${DB_HOST:-host.docker.internal}" \
    --port="${DB_PORT:-3306}" \
    --user="${DB_USERNAME:-icybreeze_app}" \
    --password="${DB_PASSWORD:-}" \
    --silent; do
    if [ "$attempt" -ge 30 ]; then
        echo "MySQL did not become ready after 30 attempts." >&2
        exit 1
    fi

    echo "Waiting for MySQL (${attempt}/30)..."
    attempt=$((attempt + 1))
    sleep 2
done

php artisan migrate --force --no-interaction

if [ "${RUN_SEEDER:-true}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

if [ -n "${ADMIN_EMAIL:-}" ] && [ -n "${ADMIN_PASSWORD:-}" ]; then
    if [ -n "${ADMIN_PHONE:-}" ]; then
        php artisan app:create-admin \
            "$ADMIN_EMAIL" \
            "--name=${ADMIN_NAME:-IcyBreeze Administrator}" \
            "--phone=$ADMIN_PHONE" \
            "--password=$ADMIN_PASSWORD"
    else
        php artisan app:create-admin \
            "$ADMIN_EMAIL" \
            "--name=${ADMIN_NAME:-IcyBreeze Administrator}" \
            "--password=$ADMIN_PASSWORD"
    fi
fi

php artisan config:cache --no-interaction
php artisan view:cache --no-interaction

if [ "${1:-}" = "apache2-foreground" ]; then
    find /etc/apache2/mods-enabled -maxdepth 1 -name 'mpm_*' -delete
    ln -s ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
    ln -s ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

    apache2ctl configtest
fi

exec "$@"
