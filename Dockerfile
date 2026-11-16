# syntax=docker/dockerfile:1
#
# Textileria ERP - Symfony 8.2 sobre PHP 8.4.27 + Apache
#
#   Etapas:
#     base  -> PHP 8.4.27 + extensiones + Apache + Composer
#     dev   -> desarrollo local (codigo montado como volumen, Xdebug opcional)
#     prod  -> imagen inmutable para despliegue remoto (codigo copiado, sin dev-deps)
#
ARG PHP_VERSION=8.4.27

###############################################################################
# BASE
###############################################################################
FROM php:${PHP_VERSION}-apache AS base

ENV APP_DIR=/var/www/html \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    APACHE_DOCUMENT_ROOT=/var/www/html/public

WORKDIR ${APP_DIR}

# Dependencias del sistema + extensiones PHP requeridas por Symfony,
# Doctrine DBAL (pdo_mysql) e intl
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git unzip acl tzdata \
        libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
        libxml2-dev libonig-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        intl zip gd pdo_mysql opcache bcmath; \
    rm -rf /var/lib/apt/lists/* /tmp/pear

# Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache: DocumentRoot -> public/, mod_rewrite, cabeceras
RUN set -eux; \
    a2enmod rewrite headers expires; \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf; \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Configuracion PHP comun
COPY docker/php/conf.d/app.ini $PHP_INI_DIR/conf.d/zz-app.ini

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint
# (quita CRLF por si el archivo se edito en Windows)
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint && chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/health") === false ? 1 : 0);'

ENTRYPOINT ["docker-entrypoint"]
CMD ["apache2-foreground"]

###############################################################################
# DEV
###############################################################################
FROM base AS dev

ENV APP_ENV=dev APP_DEBUG=1

RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY docker/php/conf.d/app.dev.ini $PHP_INI_DIR/conf.d/zz-app.dev.ini

# Xdebug opcional: docker compose build --build-arg INSTALL_XDEBUG=1
ARG INSTALL_XDEBUG=0
RUN if [ "$INSTALL_XDEBUG" = "1" ]; then \
        pecl install xdebug && docker-php-ext-enable xdebug; \
    fi

###############################################################################
# PROD
###############################################################################
FROM base AS prod

ENV APP_ENV=prod APP_DEBUG=0

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/zz-app.prod.ini

# 1) Dependencias (capa cacheable)
COPY composer.json composer.lock symfony.lock ./
RUN set -eux; \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress --no-interaction

# 2) Codigo de la aplicacion
COPY . .

RUN set -eux; \
    mkdir -p var/cache var/log var/sessions; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    composer dump-env prod; \
    composer run-script --no-dev post-install-cmd; \
    chown -R www-data:www-data var
