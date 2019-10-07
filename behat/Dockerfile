FROM composer AS composer
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
RUN apk add --no-cache postgresql-client libzip-dev unzip

COPY --from=composer /app/vendor vendor
COPY --from=composer /app/composer.json composer.json
COPY --from=composer /app/composer.lock composer.lock
COPY tests tests

ENTRYPOINT [ "vendor/bin/behat", "--config=tests/behat.yml", "--stop-on-failure" ]
