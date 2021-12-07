FROM node:14.8.0-alpine AS webpack
WORKDIR /app

COPY package.json .
COPY package-lock.json .
COPY webpack.config.js .
COPY templates templates
COPY assets assets
COPY translations translations

# Install NPM dependencies
RUN npm install

# Check linting
RUN npm run lint
RUN npm audit --production

#Build assets
RUN NODE_ENV=production npm run build

FROM composer:2.1.2 AS composer
WORKDIR /app

RUN apk update

RUN apk add libpng libpng-dev libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev

RUN composer --version
# Install composer dependencies
COPY composer.json .
COPY composer.lock .
# Remove '--ignore-platform-req=php' when we move to php 8
RUN composer install --prefer-dist --no-interaction --no-scripts --ignore-platform-req=php

COPY app app
COPY config config
COPY src src
RUN composer run-script post-install-cmd --no-interaction
RUN composer dump-autoload --optimize

FROM php:8.0.12-fpm-alpine3.14
WORKDIR /var/www
EXPOSE 80
EXPOSE 443
ENV TIMEOUT=60
ENV PHP_EXT_DIR=/usr/local/lib/php/extensions/no-debug-non-zts-20200930/

# Install core PHP extensions
RUN apk add --no-cache openssl nginx nginx-mod-http-headers-more su-exec postgresql-client libzip-dev unzip php7-igbinary php7-pecl-redis
RUN docker-php-ext-install pcntl zip opcache
RUN docker-php-ext-enable opcache

# Install pcov for faster coverage tooling
RUN apk add --no-cache autoconf build-base
RUN pecl install pcov && docker-php-ext-enable pcov

# Install Xdebug if directed to with build arg from docker-compose.yml
ARG REQUIRE_XDEBUG=0
RUN if [[ $REQUIRE_XDEBUG = 1 ]] ; then \
  pecl install xdebug-3.1.1  ;\
  docker-php-ext-enable $PHP_EXT_DIR/xdebug.so ; \
  echo "xdebug.mode = develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
  echo "xdebug.discover_client_host = true" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
  echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
  echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
  echo "xdebug.log = /tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
  echo "xdebug.idekey = PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
  fi ;

# Route NGINX logs to stdout/stderr
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log \
    && ln -s /etc/nginx/http.d /etc/nginx/conf.d

# Add Confd to configure parameters on start
ENV CONFD_VERSION="0.16.0"
RUN wget -q -O /usr/local/bin/confd "https://github.com/kelseyhightower/confd/releases/download/v${CONFD_VERSION}/confd-${CONFD_VERSION}-linux-amd64" \
    && chmod +x /usr/local/bin/confd

# Add Waitforit to wait on API starting
ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
    && chmod +x /usr/local/bin/waitforit

# Generate certificate
RUN mkdir -p /etc/nginx/certs
RUN openssl req -newkey rsa:4096 -x509 -nodes -keyout /etc/nginx/certs/app.key -new -out /etc/nginx/certs/app.crt -subj "/C=GB/ST=GB/L=London/O=OPG/OU=Digital/CN=default" -sha256 -days "3650"

RUN mkdir -p var/cache \
    && mkdir -p var/logs \
    && mkdir -p /var/log/app \
    && chown -R www-data var \
    && chown -R www-data /var/log/app

COPY --from=composer /app/app app
COPY --from=composer /app/vendor/bin bin
COPY --from=composer /app/vendor vendor
COPY --from=composer /app/composer.lock composer.lock
COPY --from=composer /app/config/parameters.yml config/parameters.yml
COPY config config
COPY public public
COPY --from=webpack /app/public/assets public/assets
COPY --from=webpack /app/public/images public/images
COPY scripts scripts
COPY src src
COPY templates templates
COPY assets assets
COPY translations translations
COPY tests tests
COPY phpstan.neon .
COPY docker/confd /etc/confd
COPY frontend.env frontend.env
COPY admin.env admin.env

RUN chown -R www-data scripts

# Add common passwords file. Two options to combat flakey responses from site.
COPY docker/extra/commonpasswords.txt /tmp/commonpasswords.txt
RUN wget -q -O /tmp/commonpasswords.txt "https://www.ncsc.gov.uk/static-assets/documents/PwnedPasswordsTop100k.txt" \
    || echo 'using local copy of pwnedpasswords' \
    && chown www-data /tmp/commonpasswords.txt

#Check for security issues
RUN wget -q -O local-php-security-checker https://github.com/fabpot/local-php-security-checker/releases/download/v1.0.0/local-php-security-checker_1.0.0_linux_arm64 \
    && chmod +x local-php-security-checker \
    && su-exec local-php-security-checker

# Prebuild cache
RUN su-exec www-data php -d memory_limit=-1 app/console cache:warmup

CMD confd -onetime -backend env \
    && waitforit -address=$API_URL/manage/availability -timeout=$TIMEOUT -insecure \
    && php-fpm -D \
    && nginx
