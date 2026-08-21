# syntax=docker/dockerfile:1.7

FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build


FROM php:8.3-apache-bookworm AS application

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    APP_NAME=IcyBreeze \
    APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost:8080 \
    APP_TIMEZONE=Asia/Manila \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=mysql \
    DB_HOST=host.docker.internal \
    DB_PORT=3306 \
    DB_DATABASE=icybreeze_scheduling \
    DB_USERNAME=icybreeze_app \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database \
    RUN_SEEDER=true

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libcurl4-openssl-dev \
        libicu-dev \
        libonig-dev \
        default-mysql-client \
        libsqlite3-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" curl dom intl mbstring opcache pdo_mysql pdo_sqlite xml zip \
    && a2enmod expires headers rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    apt-get update \
    && apt-get install -y --no-install-recommends git \
    && COMPOSER_CACHE_DIR=/tmp/composer-cache \
    COMPOSER_MAX_PARALLEL_HTTP=2 \
    composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader \
    --prefer-source \
    && find vendor -type d -name .git -prune -exec rm -rf '{}' + \
    && apt-get purge -y --auto-remove git \
    && rm -rf /var/lib/apt/lists/*

COPY . .
COPY --from=frontend /app/public/build ./public/build
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/icybreeze.ini
COPY docker/entrypoint.sh /usr/local/bin/icybreeze-entrypoint

RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction --no-scripts \
    && php artisan package:discover --ansi \
    && mkdir -p \
        bootstrap/cache \
        database \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        /var/www/html/.docker-runtime \
    && chmod +x /usr/local/bin/icybreeze-entrypoint \
    && chown -R www-data:www-data \
        bootstrap/cache \
        database \
        storage \
        /var/www/html/.docker-runtime

EXPOSE 80

HEALTHCHECK --interval=15s --timeout=5s --start-period=30s --retries=5 \
    CMD curl --fail --silent --show-error http://127.0.0.1/up || exit 1

ENTRYPOINT ["icybreeze-entrypoint"]
CMD ["apache2-foreground"]
