##############################
# Stage 1: Base Composer Setup
##############################
FROM composer:2.8.9 AS composer
WORKDIR /app

COPY client/app/composer.json .
COPY client/app/composer.lock .
COPY client/app/app app
COPY client/app/config config
COPY client/app/src src

##############################
# Stage 1a: Composer Runtime (no dev)
##############################
FROM composer AS composer-runtime
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts
RUN composer check-platform-reqs
RUN composer dump-autoload --optimize

##############################
# Stage 1b: Composer Devtools (with dev)
##############################
FROM composer AS composer-devtools
RUN composer install --prefer-dist --no-interaction --no-scripts
RUN composer check-platform-reqs
RUN composer dump-autoload --optimize

##############################
# Stage 2: Base PHP + Shared Files
##############################
FROM php:8.3.21-fpm-alpine3.21 AS base
ARG PLATFORM=${PLATFORM:-amd64}
WORKDIR /var/www
ENV TIMEOUT=60

# Install dependencies
RUN apk update && apk upgrade && apk add --no-cache \
    su-exec \
    libzip-dev \
    unzip \
    php82-pecl-igbinary \
    php82-pecl-redis \
    php82-pecl-imagick \
    gmp \
    gmp-dev \
    libheif \
    libde265 \
    ffmpeg \
    autoconf \
    build-base \
    wget

RUN docker-php-ext-install pcntl zip opcache gmp && docker-php-ext-enable opcache
RUN pecl install pcov && docker-php-ext-enable pcov

# Xdebug toggle
ARG REQUIRE_XDEBUG=0
RUN if [ "$REQUIRE_XDEBUG" = "1" ]; then \
    apk add --update linux-headers && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    apk del linux-headers; \
  fi

# Cache/log directories
RUN mkdir -p var/cache var/logs /var/log/app && chown -R www-data var /var/log/app

# Add pwned passwords
COPY client/docker/app/extra/commonpasswords.txt /tmp/commonpasswords.txt
RUN wget -q -O /tmp/commonpasswords.txt "https://www.ncsc.gov.uk/static-assets/documents/PwnedPasswordsTop100k.txt" || echo "Using local copy" \
 && chown www-data /tmp/commonpasswords.txt

# Add shared application assets/config files
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

##############################
# Stage 3a: Runtime (production-only, non-root)
##############################
FROM base AS runtime
COPY --chown=www-data:www-data --from=composer-runtime /app/vendor vendor
COPY --chown=www-data:www-data --from=composer-runtime /app/app app
COPY --chown=www-data:www-data --from=composer-runtime /app/config config
COPY --chown=www-data:www-data --from=composer-runtime /app/src src
COPY --chown=www-data:www-data --from=composer-runtime /app/composer.lock composer.lock

# Shared files from base
COPY --from=base /var/www/public public
COPY --from=base /var/www/scripts scripts
COPY --from=base /var/www/templates templates
COPY --from=base /var/www/assets assets
COPY --from=base /var/www/translations translations
COPY --from=base /var/www/frontend.env frontend.env
COPY --from=base /var/www/admin.env admin.env
COPY --from=base /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --from=base /var/www/public/meta.json.tmpl public/meta.json.tmpl
COPY --from=base /var/www/generate_parameters_yml.sh generate_parameters_yml.sh
COPY --from=base /usr/local/etc/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN sed "s|TAG|${TAG:-latest}|" public/meta.json.tmpl > public/meta.json
RUN ./generate_parameters_yml.sh && mv /var/www/parameters.yml config/parameters.yml
RUN su-exec www-data php -d memory_limit=-1 app/console cache:warmup --env=prod --no-debug

COPY scripts/hardening/harden.sh /harden.sh
RUN /harden.sh www-data && rm /harden.sh

USER www-data
CMD ["sh", "-c", "php-fpm"]

##############################
# Stage 3b: Devtools Base (with dev dependencies)
##############################
FROM base AS devtools-base
COPY --chown=www-data:www-data --from=composer-devtools /app/vendor vendor
COPY --chown=www-data:www-data --from=composer-devtools /app/app app
COPY --chown=www-data:www-data --from=composer-devtools /app/config config
COPY --chown=www-data:www-data --from=composer-devtools /app/src src
COPY --chown=www-data:www-data --from=composer-devtools /app/composer.lock composer.lock

COPY --chown=www-data:www-data client/app/tests tests
COPY --chown=www-data:www-data client/app/phpstan.neon .
COPY --chown=www-data:www-data client/app/phpstan-baseline.neon .

# Shared files from base
COPY --from=base /var/www/public public
COPY --from=base /var/www/scripts scripts
COPY --from=base /var/www/templates templates
COPY --from=base /var/www/assets assets
COPY --from=base /var/www/translations translations
COPY --from=base /var/www/frontend.env frontend.env
COPY --from=base /var/www/admin.env admin.env
COPY --from=base /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --from=base /var/www/public/meta.json.tmpl public/meta.json.tmpl
COPY --from=base /var/www/generate_parameters_yml.sh generate_parameters_yml.sh
COPY --from=base /usr/local/etc/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN sed "s|TAG|${TAG:-latest}|" public/meta.json.tmpl > public/meta.json
RUN ./generate_parameters_yml.sh && mv /var/www/parameters.yml config/parameters.yml
RUN su-exec www-data php -d memory_limit=-1 app/console cache:warmup

##############################
# Stage 3b1: Devtools (non-root)
##############################
FROM devtools-base AS devtools
RUN chown -R www-data:www-data /var/www/var && chmod -R ug+rwX /var/www/var
COPY scripts/hardening/harden.sh /harden.sh
RUN /harden.sh www-data && rm /harden.sh
USER www-data
CMD ["sh", "-c", "php-fpm"]

##############################
# Stage 3b2: Devtools Admin (root)
##############################
FROM devtools-base AS devtools-admin
USER root
CMD ["sh", "-c", "php-fpm"]
