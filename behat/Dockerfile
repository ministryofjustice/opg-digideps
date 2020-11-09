FROM composer:1.10.16 AS composer
WORKDIR /app

# Make composer super fast
RUN composer global require hirak/prestissimo --no-plugins --no-scripts

# Install composer dependencies
COPY composer.json .
COPY composer.lock .

RUN composer install --prefer-dist --no-interaction --no-scripts
RUN composer dump-autoload --optimize


FROM php:alpine
WORKDIR /var/www

# Install dependencies
RUN apk add --no-cache postgresql-client libzip-dev unzip bash

RUN mkdir -p /tmp/html
RUN mkdir -p /tmp/sql

COPY --from=composer /app/vendor vendor
COPY --from=composer /app/composer.json composer.json
COPY --from=composer /app/composer.lock composer.lock
COPY tests tests

COPY docker-entrypoint.sh /usr/local/bin/
RUN ["chmod", "+x", "/usr/local/bin/docker-entrypoint.sh"]

ENTRYPOINT [ "docker-entrypoint.sh" ]
