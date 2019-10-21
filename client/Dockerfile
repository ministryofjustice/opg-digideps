FROM node:8-alpine AS webpack
WORKDIR /app

COPY package.json .
COPY package-lock.json .
COPY webpack.config.js .
COPY .sass-lint.yml .
COPY src src

# Install NPM dependencies
RUN npm install

# Check linting
RUN npm run lint
RUN npm audit

# Build assets
RUN NODE_ENV=production npm run build

FROM composer AS composer
WORKDIR /app

# Make composer super fast
RUN composer global require hirak/prestissimo --no-plugins --no-scripts

# Install composer dependencies
COPY composer.json .
COPY composer.lock .
RUN composer install --prefer-dist --no-interaction --no-scripts

COPY app app
COPY src src
RUN composer run-script post-install-cmd --no-interaction
RUN composer dump-autoload --optimize

FROM php:7-fpm-alpine
WORKDIR /var/www
EXPOSE 80
EXPOSE 443
ENV TIMEOUT=60
ENV PHP_EXT_DIR=/usr/lib/php7/modules

# Install core PHP extensions
RUN apk add --no-cache openssl nginx nginx-mod-http-headers-more su-exec postgresql-client libzip-dev unzip php7-igbinary php7-pecl-redis
RUN docker-php-ext-enable opcache $PHP_EXT_DIR/igbinary.so $PHP_EXT_DIR/redis.so
RUN docker-php-ext-install zip

# Install Xdebug if directed to with build arg from docker-compose.yml
ARG REQUIRE_XDEBUG=false
RUN if [[ $REQUIRE_XDEBUG = "true" ]] ; then \
        apk add --no-cache php7-pecl-xdebug; \
        docker-php-ext-enable $PHP_EXT_DIR/xdebug.so; \
    fi ;

# Route NGINX logs to stdout/stderr
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
  && ln -sf /dev/stderr /var/log/nginx/error.log

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
  && chown -R www-data var

CMD confd -onetime -backend env \
  && waitforit -address=$FRONTEND_API_URL/manage/availability -timeout=$TIMEOUT -insecure \
  && php-fpm -D \
  && nginx

# See this page for directories required
# https://symfony.com/doc/3.4/quick_tour/the_architecture.html
COPY --from=composer /app/app app
COPY --from=composer /app/vendor vendor
COPY --from=composer /app/composer.lock composer.lock
COPY bin bin
COPY scripts scripts
COPY src src
COPY tests tests
COPY web web
COPY --from=webpack /app/web/assets web/assets
COPY --from=webpack /app/web/images web/images
COPY docker/confd /etc/confd

# Check for security issues
RUN su-exec www-data php app/console security:check
