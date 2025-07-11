FROM composer:2.5.7 AS composer

WORKDIR /app
# Install composer dependencies
COPY client/app/composer.json .
COPY client/app/composer.lock .
RUN composer install --prefer-dist --no-interaction --no-scripts
RUN composer check-platform-reqs
COPY client/app/app app
COPY client/app/config config
COPY client/app/src src
RUN composer dump-autoload --optimize

FROM php:8.3.21-fpm-alpine3.21 AS base
ARG PLATFORM=${PLATFORM:-amd64}
ARG TAG=${TAG:-latest}
ARG ENVIRONMENT=${ENVIRONMENT:-production}
WORKDIR /var/www
ENV TIMEOUT=60
# Install core PHP extensions
RUN apk update && apk upgrade
RUN apk add --no-cache \
    su-exec \
    libzip-dev \
    unzip \
    php82-pecl-igbinary \
    php82-pecl-redis \
    php82-pecl-imagick  \
    gmp \
    gmp-dev \
    libheif \
    libde265 \
    ffmpeg

# Install necessary PHP extensions
RUN docker-php-ext-install pcntl zip opcache gmp
RUN docker-php-ext-enable opcache
# Install pcov for faster coverage tooling
RUN apk add --no-cache autoconf build-base
RUN pecl install pcov && docker-php-ext-enable pcov
# Install Xdebug if directed to with build arg from docker-compose.yml
ARG REQUIRE_XDEBUG=0
RUN if [[ $REQUIRE_XDEBUG = 1 ]] ; then \
    apk add --update linux-headers && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    apk del linux-headers; \
fi ;

# Create var directories
RUN mkdir -p var/cache \
    && mkdir -p var/logs \
    && mkdir -p /var/log/app \
    && chown -R www-data var \
    && chown -R www-data /var/log/app
# Add common passwords file. Two options to combat flakey responses from site.
COPY client/docker/app/extra/commonpasswords.txt /tmp/commonpasswords.txt
RUN wget -q -O /tmp/commonpasswords.txt "https://www.ncsc.gov.uk/static-assets/documents/PwnedPasswordsTop100k.txt" \
    || echo 'using local copy of pwnedpasswords' \
    && chown www-data /tmp/commonpasswords.txt

FROM base AS application
COPY --chown=www-data:www-data --from=composer /app/app app
COPY --chown=www-data:www-data --from=composer /app/vendor/bin bin
COPY --chown=www-data:www-data --from=composer /app/vendor vendor
COPY --chown=www-data:www-data --from=composer /app/composer.lock composer.lock
COPY --chown=www-data:www-data client/app/config config
COPY --chown=www-data:www-data client/app/public public
COPY --chown=www-data:www-data client/resources/public/ public/
COPY --chown=www-data:www-data client/app/scripts scripts
COPY --chown=www-data:www-data client/app/src src
COPY --chown=www-data:www-data client/app/templates templates
COPY --chown=www-data:www-data client/resources/assets assets
COPY --chown=www-data:www-data client/app/translations translations
COPY --chown=www-data:www-data client/app/tests tests
COPY --chown=www-data:www-data client/app/phpstan.neon .
COPY --chown=www-data:www-data client/app/phpstan-baseline.neon .
COPY --chown=www-data:www-data client/app/frontend.env frontend.env
COPY --chown=www-data:www-data client/app/admin.env admin.env
COPY --chown=www-data:www-data client/docker/app/config/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --chown=www-data:www-data client/docker/app/config/meta.json.tmpl /var/www/public/meta.json.tmpl
COPY --chown=www-data:www-data client/docker/app/config/generate_parameters_yml.sh generate_parameters_yml.sh
COPY --chown=www-data:www-data client/docker/app/config/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
RUN chown -R www-data:www-data scripts \
    && sed "s|TAG|${TAG}|" /var/www/public/meta.json.tmpl > /var/www/public/meta.json
RUN ./generate_parameters_yml.sh && mv /var/www/parameters.yml /var/www/config/parameters.yml

# Prebuild cache
RUN su-exec www-data php -d memory_limit=-1 app/console cache:warmup

FROM application AS ci-tests
# We use this setup for certain commands as part of the CI processing of unit tests
CMD ["sh", "-c", "php-fpm"]

FROM application AS production
COPY scripts/hardening/harden.sh /harden.sh
RUN /harden.sh www-data && rm /harden.sh

USER www-data

CMD ["sh", "-c", "php-fpm"]
