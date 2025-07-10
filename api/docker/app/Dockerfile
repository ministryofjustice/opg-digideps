# Set default to production image unless overridden
ARG PROD_IMAGE=true

##############################
# Stage 1: Composer Build
##############################
FROM composer:2.8.9 AS composer

ARG PROD_IMAGE
WORKDIR /app

COPY api/app/composer.json .
COPY api/app/composer.lock .

# Install dependencies conditionally
RUN if [ "$PROD_IMAGE" = "true" ]; then \
      composer install --no-dev --prefer-dist --no-interaction --no-scripts; \
    else \
      composer install --prefer-dist --no-interaction --no-scripts; \
    fi

RUN composer check-platform-reqs

COPY api/app/app app
COPY api/app/config config
COPY api/app/src src

RUN composer dump-autoload --optimize


##############################
# Stage 2: Base PHP Image
##############################
FROM php:8.3.21-fpm-alpine3.21 AS base

ARG PLATFORM=${PLATFORM:-amd64}
ARG ENVIRONMENT=${ENVIRONMENT:-production}
WORKDIR /var/www
EXPOSE 80
EXPOSE 443

ENV TIMEOUT=20

RUN apk --no-cache add \
    postgresql-dev \
    postgresql-client \
    openssl \
    su-exec \
    php82-pecl-igbinary \
    php82-pecl-redis \
    autoconf \
    build-base \
    wget

RUN docker-php-ext-install pdo pdo_pgsql opcache \
 && docker-php-ext-enable opcache

# Install optional Xdebug or PCOV
ARG REQUIRE_XDEBUG=0
RUN if [ "$REQUIRE_XDEBUG" = "1" ]; then \
    apk add --update linux-headers && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    apk del linux-headers; \
  else \
    pecl install pcov && \
    docker-php-ext-enable pcov; \
  fi

RUN mkdir -p var/cache var/logs /var/log/app \
 && chown -R www-data var /var/log/app


##############################
# Stage 3: Application (Shared)
##############################
FROM base AS application

COPY --chown=www-data:www-data --from=composer /app/vendor vendor
COPY --chown=www-data:www-data --from=composer /app/app app
COPY --chown=www-data:www-data --from=composer /app/config config
COPY --chown=www-data:www-data --from=composer /app/src src
COPY --chown=www-data:www-data --from=composer /app/composer.lock composer.lock
COPY --chown=www-data:www-data api/app/public public
COPY --chown=www-data:www-data api/app/scripts scripts
COPY --chown=www-data:www-data api/app/api.env api.env
COPY --chown=www-data:www-data api/app/postgres.env postgres.env
COPY --chown=www-data:www-data api/app/scripts/wait-for-db.sh wait-for-db.sh
COPY --chown=www-data:www-data api/docker/app/config/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --chown=www-data:www-data api/docker/app/config/generate_parameters_yml.sh generate_parameters_yml.sh
COPY --chown=www-data:www-data api/docker/app/config/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN ./generate_parameters_yml.sh && mv /var/www/parameters.yml /var/www/config/parameters.yml
RUN chmod 544 /var/www/wait-for-db.sh

RUN mkdir certs && chmod 755 certs \
 && wget -O certs/eu-west-1-bundle.pem https://truststore.pki.rds.amazonaws.com/eu-west-1/eu-west-1-bundle.pem


##############################
# Stage 4a: Production Image
##############################
FROM application AS production

ARG PROD_IMAGE
RUN if [ "$PROD_IMAGE" = "true" ]; then \
    echo "== Building production image =="; \
    else \
    echo "== Skipping production, use end-to-end-tests ==" && exit 1; \
    fi

# Pre warm cache
RUN su-exec www-data php -d memory_limit=-1 app/console cache:warmup --env=prod --no-debug

COPY scripts/hardening/harden.sh /harden.sh
RUN /harden.sh www-data && rm /harden.sh

USER www-data

CMD ["sh", "-c", "/var/www/wait-for-db.sh -h $DATABASE_HOSTNAME -p $DATABASE_PORT -t $TIMEOUT \
    && php app/console doctrine:migrations:migrate --allow-no-migration --no-interaction \
    && php app/console doctrine:migrations:up-to-date \
    && php-fpm"]


##############################
# Stage 4b: tests
##############################
FROM application AS tests

# Only in test builds: copy test code & tools
COPY --chown=www-data:www-data api/app/tests tests
COPY --chown=www-data:www-data api/app/phpstan.neon .
COPY --chown=www-data:www-data api/app/phpstan-baseline.neon .
# Make Behat test scripts executable
RUN chmod 544 /var/www/tests/Behat/run-tests*.sh
# Pre warm cache
RUN su-exec www-data php -d memory_limit=-1 app/console cache:warmup

##############################
# Stage 4b-1: End-to-End Tests
##############################
FROM tests AS end-to-end-tests
ARG PROD_IMAGE
RUN if [ "$PROD_IMAGE" = "false" ]; then \
    echo "== Building end-to-end test image =="; \
    else \
    echo "== Skipping end-to-end-tests, use production ==" && exit 1; \
    fi \

COPY scripts/hardening/harden.sh /harden.sh
RUN /harden.sh www-data && rm /harden.sh

USER www-data

CMD ["sh", "-c", "/var/www/wait-for-db.sh -h $DATABASE_HOSTNAME -p $DATABASE_PORT -t $TIMEOUT \
    && php app/console doctrine:migrations:migrate --allow-no-migration --no-interaction \
    && php app/console doctrine:migrations:up-to-date \
    && php-fpm"]

##############################
# Stage 4b-1: CI-Tests
##############################
FROM tests AS ci-tests
USER www-data
CMD ["sh", "-c", "/var/www/wait-for-db.sh -h $DATABASE_HOSTNAME -p $DATABASE_PORT -t $TIMEOUT \
    && php app/console doctrine:migrations:migrate --allow-no-migration --no-interaction \
    && php app/console doctrine:migrations:up-to-date \
    && php-fpm"]
