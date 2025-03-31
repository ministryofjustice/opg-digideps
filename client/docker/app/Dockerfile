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

FROM php:8.1.28-fpm-alpine3.19 AS base
ARG PLATFORM=${PLATFORM:-amd64}
ARG TAG=${TAG:-latest}
ARG ENVIRONMENT=${ENVIRONMENT:-production}
WORKDIR /var/www
ENV TIMEOUT=60
ENV PHP_EXT_DIR=/usr/local/lib/php/extensions/no-debug-non-zts-20210902/
# Install core PHP extensions
RUN apk update && apk upgrade
RUN apk add --no-cache \
    su-exec \
    libzip-dev \
    unzip \
    php81-pecl-igbinary \
    php81-pecl-redis \
    php81-pecl-imagick  \
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
ARG XDEBUG_IDEKEY_CLIENT=PHPSTORM
RUN if [[ $REQUIRE_XDEBUG = 1 ]] ; then \
        pecl install xdebug-3.1.4  ;\
        docker-php-ext-enable $PHP_EXT_DIR/xdebug.so ; \
        echo "xdebug.mode = develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
        echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
        echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
        echo "xdebug.log = /tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
        echo "xdebug.idekey = ${XDEBUG_IDEKEY_CLIENT}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
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
COPY --chown=www-data:www-data client/app/frontend.env frontend.env
COPY --chown=www-data:www-data client/app/admin.env admin.env
COPY --chown=www-data:www-data client/docker/app/config/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --chown=www-data:www-data client/docker/app/config/meta.json.tmpl /var/www/public/meta.json.tmpl
COPY --chown=www-data:www-data client/docker/app/config/generate_parameters_yml.sh generate_parameters_yml.sh
RUN chown -R www-data:www-data scripts \
    && sed "s|TAG|${TAG}|" /var/www/public/meta.json.tmpl > /var/www/public/meta.json
RUN ./generate_parameters_yml.sh && mv /var/www/parameters.yml /var/www/config/parameters.yml

RUN su-exec www-data php -d memory_limit=-1 app/console cache:warmup \
    && mkdir -p /var/www/cache_backup && chown www-data:www-data /var/www/cache_backup \
    && cp -R /var/www/var/cache/. /var/www/cache_backup/

#FROM application AS ci-tests
## We use this setup for certain commands as part of the CI processing of unit tests
#CMD ["sh", "-c", "php-fpm"]
#
#FROM application AS production
#COPY scripts/hardening/harden.sh /harden.sh
#RUN /harden.sh www-data && rm /harden.sh

USER www-data

CMD ["sh", "-c", "cp -R /var/www/cache_backup/. /var/www/var/cache/ && php-fpm"]
#CMD ["sh", "-c", "php-fpm"]

#COPY ./docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
#RUN chmod +x /usr/local/bin/docker-entrypoint.sh
#
#
#ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
## This is the command that will be passed to the entrypoint script's "$@"
#CMD ["php-fpm"]
