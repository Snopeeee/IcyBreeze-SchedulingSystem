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

if [ -n "${RENDER_EXTERNAL_HOSTNAME:-}" ]; then
    APP_URL="https://${RENDER_EXTERNAL_HOSTNAME}"
    export APP_URL
fi

if [ -z "${APP_KEY:-}" ] && [ -n "${RENDER_APP_KEY:-}" ]; then
    APP_KEY="base64:${RENDER_APP_KEY}"
    export APP_KEY
fi

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
while :; do
    if [ "${DB_CONNECTION:-mysql}" = "pgsql" ]; then
        if [ -n "${DB_URL:-}" ]; then
            pg_isready -d "$DB_URL" -q && break
        else
            pg_isready -h "${DB_HOST:-127.0.0.1}" -p "${DB_PORT:-5432}" \
                -U "${DB_USERNAME:-postgres}" -d "${DB_DATABASE:-postgres}" -q && break
        fi
    else
        mysqladmin ping \
            --host="${DB_HOST:-host.docker.internal}" \
            --port="${DB_PORT:-3306}" \
            --user="${DB_USERNAME:-icybreeze_app}" \
            --password="${DB_PASSWORD:-}" \
            --silent && break
    fi

    if [ "$attempt" -ge 30 ]; then
        echo "Database did not become ready after 30 attempts." >&2
        exit 1
    fi

    echo "Waiting for database (${attempt}/30)..."
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
    if [ -n "${PORT:-}" ] && [ "$PORT" != "80" ]; then
        case "$PORT" in
            *[!0-9]*) echo "PORT must be numeric." >&2; exit 1 ;;
        esac
        sed -i "s/^Listen 80$/Listen $PORT/" /etc/apache2/ports.conf
        sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf
    fi

    find /etc/apache2/mods-enabled -maxdepth 1 -name 'mpm_*' -delete
    ln -s ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
    ln -s ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

    apache2ctl configtest
fi

exec "$@"
