FROM composer:2.3.5 AS composer

WORKDIR /app

# Install composer dependencies
COPY composer.json .
COPY composer.lock .
# Remove '--ignore-platform-req=php' when we move to php 8
RUN composer install --prefer-dist --no-interaction --no-scripts --ignore-platform-reqs

COPY app app
COPY config config
COPY src src

RUN composer run-script post-install-cmd --no-interaction

RUN composer dump-autoload --optimize

FROM php:8.0.19-fpm-alpine3.15
WORKDIR /var/www
EXPOSE 80
EXPOSE 443
ENV TIMEOUT=20
ENV PHP_EXT_DIR=/usr/local/lib/php/extensions/no-debug-non-zts-20200930/

RUN apk --no-cache add \
  postgresql-dev \
  postgresql-client \
  openssl \
  nginx \
  nginx-mod-http-headers-more \
  su-exec \
  php7-igbinary \
  php7-pecl-redis

RUN docker-php-ext-install pdo pdo_pgsql opcache
RUN docker-php-ext-enable opcache

# Install pcov for faster coverage tooling
RUN apk add --no-cache autoconf build-base
RUN pecl install pcov && docker-php-ext-enable pcov

# Install Xdebug if directed to with build arg from docker-compose.yml
ARG REQUIRE_XDEBUG=0
RUN if [[ $REQUIRE_XDEBUG = 1 ]] ; then \
  pecl install xdebug-3.1.4  ;\
  docker-php-ext-enable $PHP_EXT_DIR/xdebug.so ; \
  echo "xdebug.mode = develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; \
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

# Add Waitforit to wait on db starting
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

COPY docker/confd /etc/confd
COPY --from=composer /app/app app
COPY --from=composer /app/vendor/bin bin
COPY --from=composer /app/vendor vendor
COPY --from=composer /app/composer.lock composer.lock
COPY --from=composer /app/config/parameters.yml config/parameters.yml
COPY app app
COPY config config
COPY public public
COPY scripts scripts
COPY src src
COPY tests tests
COPY api.env api.env
COPY postgres.env postgres.env
COPY phpstan.neon .

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

# Check for security issues
RUN wget -q -O local-php-security-checker https://github.com/fabpot/local-php-security-checker/releases/download/v1.0.0/local-php-security-checker_1.0.0_linux_arm64 \
  && chmod +x local-php-security-checker \
  && su-exec local-php-security-checker

RUN apk upgrade libxml2 openssl libssl1.1 libcrypto1.1 libretls zlib busybox curl gzip xz xz-libs libcurl

CMD confd -onetime -backend env \
  && waitforit -address=tcp://$DATABASE_HOSTNAME:$DATABASE_PORT -timeout=$TIMEOUT \
  && su-exec www-data:www-data php app/console doctrine:migrations:migrate --allow-no-migration --no-interaction \
  && su-exec www-data:www-data php app/console doctrine:migrations:up-to-date \
  && php-fpm -D \
  && nginx
