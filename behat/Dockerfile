FROM composer:2 AS composer
WORKDIR /app

# Install composer dependencies
COPY composer.json .
COPY composer.lock .
# Remove '--ignore-platform-req=php' when we move to php 8
RUN composer install --prefer-dist --no-interaction --no-scripts --ignore-platform-req=php
RUN composer dump-autoload --optimize


FROM php:7.4.16-alpine
WORKDIR /var/www

# Install dependencies
RUN apk add --no-cache postgresql-client libzip-dev unzip

RUN mkdir -p /tmp/html
RUN mkdir -p /tmp/sql

COPY --from=composer /app/vendor vendor
COPY --from=composer /app/composer.json composer.json
COPY --from=composer /app/composer.lock composer.lock
COPY tests tests

COPY docker-entrypoint.sh /usr/local/bin/
COPY docker-smoke-test-entrypoint.sh /usr/local/bin/
RUN ["chmod", "+x", "/usr/local/bin/docker-entrypoint.sh"]
RUN ["chmod", "+x", "/usr/local/bin/docker-smoke-test-entrypoint.sh"]

ENTRYPOINT [ "docker-entrypoint.sh" ]
