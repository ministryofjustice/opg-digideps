FROM composer:2.5.7 AS composer

WORKDIR /app
# Install composer dependencies
COPY api/app/composer.json .
COPY api/app/composer.lock .
RUN composer install --prefer-dist --no-interaction --no-scripts
RUN composer check-platform-reqs
COPY api/app/app app
COPY api/app/config config
COPY api/app/src src
RUN composer dump-autoload --optimize

FROM php:8.1.28-fpm-alpine3.19 AS base
ARG PLATFORM=${PLATFORM:-amd64}
WORKDIR /var/www
EXPOSE 80
EXPOSE 443
ENV TIMEOUT=20
ENV PHP_EXT_DIR=/usr/local/lib/php/extensions/no-debug-non-zts-20210902/
# Install required packages
RUN apk --no-cache add \
    postgresql-dev \
    postgresql-client \
    openssl \
    su-exec \
    php81-pecl-igbinary  \
    php81-pecl-redis
RUN apk update && apk upgrade
# Install necessary PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql opcache
RUN docker-php-ext-enable opcache
# Install pcov for faster coverage tooling
RUN apk add --no-cache autoconf build-base
RUN pecl install pcov && docker-php-ext-enable pcov
# Install Xdebug if directed to with build arg from docker-compose.yml
ARG REQUIRE_XDEBUG=0
ARG XDEBUG_IDEKEY_API=PHPSTORM
RUN if [[ $REQUIRE_XDEBUG = 1 ]] ; then \
    pecl install xdebug-3.1.4  ;\
    docker-php-ext-enable $PHP_EXT_DIR/xdebug.so ; \
    echo "xdebug.mode = develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
    echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
    echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
    echo "xdebug.log = /tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
    echo "xdebug.idekey = ${XDEBUG_IDEKEY_API}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
    fi ;

# Create var directories
RUN mkdir -p var/cache \
    && mkdir -p var/logs \
    && mkdir -p /var/log/app \
    && chown -R www-data var \
    && chown -R www-data /var/log/app

FROM base AS application
COPY --chown=www-data:www-data --from=composer /app/app app
COPY --chown=www-data:www-data --from=composer /app/vendor/bin bin
COPY --chown=www-data:www-data --from=composer /app/vendor vendor
COPY --chown=www-data:www-data --from=composer /app/composer.lock composer.lock
COPY --chown=www-data:www-data --from=composer /app/config/parameters.yml config/parameters.yml
COPY --chown=www-data:www-data api/app/app app
COPY --chown=www-data:www-data api/app/config config
COPY --chown=www-data:www-data api/app/public public
COPY --chown=www-data:www-data api/app/scripts scripts
COPY --chown=www-data:www-data api/app/src src
COPY --chown=www-data:www-data api/app/tests tests
COPY --chown=www-data:www-data api/app/api.env api.env
COPY --chown=www-data:www-data api/app/postgres.env postgres.env
COPY --chown=www-data:www-data api/app/phpstan.neon .
COPY --chown=www-data:www-data api/app/phpstan-baseline.neon .
COPY --chown=www-data:www-data api/app/scripts/wait-for-db.sh wait-for-db.sh
COPY --chown=www-data:www-data api/docker/app/config/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --chown=www-data:www-data api/docker/app/config/generate_parameters_yml.sh generate_parameters_yml.sh
RUN ./generate_parameters_yml.sh && mv /var/www/parameters.yml /var/www/config/parameters.yml

RUN chmod 544 /var/www/wait-for-db.sh
# Behat
RUN mkdir -p /tmp/html
RUN mkdir -p /tmp/sql
RUN chown www-data /var/www/tests/Behat/run-tests-smoke.sh
RUN chmod 544 /var/www/tests/Behat/run-tests-smoke.sh
RUN chown www-data /var/www/tests/Behat/run-tests.sh
RUN chmod 544 /var/www/tests/Behat/run-tests.sh
RUN chown www-data /var/www/tests/Behat/run-tests-parallel.sh
RUN chmod 544 /var/www/tests/Behat/run-tests-parallel.sh

# Prebuild cache
RUN su-exec www-data php -d memory_limit=-1 app/console cache:warmup

RUN mkdir certs && chmod 755 certs && wget -O certs/eu-west-1-bundle.pem https://truststore.pki.rds.amazonaws.com/eu-west-1/eu-west-1-bundle.pem

FROM application AS ci-tests
# We use this setup for certain commands as part of the CI processing of unit tests
CMD ["sh", "-c", "/var/www/wait-for-db.sh -h $DATABASE_HOSTNAME -p $DATABASE_PORT -t $TIMEOUT \
    && php app/console doctrine:migrations:migrate --allow-no-migration --no-interaction \
    && php app/console doctrine:migrations:up-to-date \
    && php-fpm"]

FROM application AS production
COPY scripts/hardening/harden.sh /harden.sh
RUN /harden.sh www-data && rm /harden.sh

USER www-data

CMD ["sh", "-c", "/var/www/wait-for-db.sh -h $DATABASE_HOSTNAME -p $DATABASE_PORT -t $TIMEOUT \
    && php app/console doctrine:migrations:migrate --allow-no-migration --no-interaction \
    && php app/console doctrine:migrations:up-to-date \
    && php-fpm"]
