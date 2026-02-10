##############################
# Stage 1: Base Composer Setup
##############################
FROM composer:2.8.12@sha256:5248900ab8b5f7f880c2d62180e40960cd87f60149ec9a1abfd62ac72a02577c AS composer

WORKDIR /app

COPY client/app/composer.json .
COPY client/app/composer.lock .
COPY client/app/app app
COPY client/app/config config
COPY client/app/src src

##############################
# Stage 1a: Composer Runtime
##############################
FROM composer AS composer-runtime
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts \
 && composer check-platform-reqs \
 && composer dump-autoload --optimize

##############################
# Stage 1b: Composer Devtools
##############################
FROM composer AS composer-devtools
RUN composer install --prefer-dist --no-interaction --no-scripts \
 && composer check-platform-reqs \
 && composer dump-autoload --optimize

##############################
# Stage 2: Base PHP + Shared Files
##############################
FROM php:8.4-fpm-alpine3.22@sha256:414f669f6be259cf658e88b08b302ca883caec21f15ac0d01e38ba512d70119f AS base

WORKDIR /var/www
ENV TIMEOUT=60

# Dependencies
RUN apk update && apk upgrade && apk add --no-cache \
    su-exec libzip-dev unzip \
    php84-pecl-igbinary php84-pecl-redis php84-pecl-imagick \
    gmp gmp-dev libheif libde265 ffmpeg \
    autoconf build-base wget \
 && docker-php-ext-install pcntl zip opcache gmp \
 && docker-php-ext-enable opcache \
 && pecl install pcov && docker-php-ext-enable pcov

# Optional Xdebug
ARG REQUIRE_XDEBUG=0
RUN if [ "$REQUIRE_XDEBUG" = "1" ]; then \
      apk add --update linux-headers && \
      pecl install xdebug && \
      docker-php-ext-enable xdebug && \
      apk del linux-headers; \
    fi

# Cache/log dirs
RUN mkdir -p var/cache var/logs /var/log/app \
 && chown -R www-data var /var/log/app

# Pwned Passwords
COPY client/docker/app/extra/commonpasswords.txt /tmp/commonpasswords.txt
RUN wget -q -O /tmp/commonpasswords.txt \
      "https://www.ncsc.gov.uk/static-assets/documents/PwnedPasswordsTop100k.txt" \
 || echo "Using local copy" \
 && chown www-data /tmp/commonpasswords.txt

# Shared app assets
COPY --chown=www-data:www-data client/app/public public
COPY --chown=www-data:www-data client/resources/public/ public/
COPY --chown=www-data:www-data client/app/scripts scripts
COPY --chown=www-data:www-data client/app/templates templates
COPY --chown=www-data:www-data client/resources/assets assets
COPY --chown=www-data:www-data client/app/translations translations
COPY --chown=www-data:www-data client/app/frontend.env frontend.env
COPY --chown=www-data:www-data client/app/admin.env admin.env
COPY --chown=www-data:www-data client/docker/app/config/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --chown=www-data:www-data client/docker/app/config/meta.json.tmpl public/meta.json.tmpl
COPY --chown=www-data:www-data client/docker/app/config/generate_parameters_yml.sh generate_parameters_yml.sh
COPY --chown=www-data:www-data client/docker/app/config/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY --chown=www-data:www-data scripts/process_checks/check_processes.sh check_processes.sh

##############################
# Stage 3a: Runtime (production)
##############################
FROM base AS runtime

COPY --chown=www-data:www-data --from=composer-runtime /app .

# Shared files from base
COPY --from=base /var/www /var/www

RUN sed "s|TAG|${TAG:-latest}|" public/meta.json.tmpl > public/meta.json \
 && ./generate_parameters_yml.sh \
 && mv parameters.yml config/parameters.yml \
 && su-exec www-data php -d memory_limit=-1 app/console cache:warmup --env=prod --no-debug

COPY scripts/hardening/harden.sh /harden.sh
RUN /harden.sh www-data && rm /harden.sh

USER www-data
CMD sh -c "./check_processes.sh & exec php-fpm"

##############################
# Stage 3b: Devtools Base
##############################
FROM base AS devtools-base

COPY --chown=www-data:www-data --from=composer-devtools /app .

COPY --chown=www-data:www-data client/app/tests tests
COPY --chown=www-data:www-data client/app/phpstan.neon .
COPY --chown=www-data:www-data client/app/phpstan-baseline.neon .

RUN sed "s|TAG|${TAG:-latest}|" public/meta.json.tmpl > public/meta.json \
 && ./generate_parameters_yml.sh \
 && mv parameters.yml config/parameters.yml \
 && su-exec www-data php -d memory_limit=-1 app/console cache:warmup

##############################
# Stage 3b1: Devtools (non-root)
##############################
FROM devtools-base AS devtools
RUN chown -R www-data:www-data var && chmod -R ug+rwX var
COPY scripts/hardening/harden.sh /harden.sh
RUN /harden.sh www-data && rm /harden.sh
USER www-data
CMD sh -c "./check_processes.sh & exec php-fpm"

##############################
# Stage 3b2: Devtools Admin (root)
##############################
FROM devtools-base AS devtools-admin
USER root
CMD sh -c "./check_processes.sh & exec php-fpm"
