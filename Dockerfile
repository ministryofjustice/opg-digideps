FROM node:8-alpine AS gulp

RUN apk add --no-cache python g++ make

WORKDIR /app
COPY package.json .
COPY package-lock.json .
COPY Gulpfile.js .
COPY src src

# Install NPM dependencies
RUN npm install

# Build assets with Gulp
RUN NODE_ENV=production npm run build



FROM composer AS composer

# Install Git for Composer
RUN apk add --no-cache git

# Install Composer
RUN  cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
RUN  composer self-update

WORKDIR /app

# Install composer dependencies
COPY composer.json .
COPY composer.lock .
RUN composer install --prefer-dist --no-interaction --no-scripts

COPY app app
COPY src src
RUN composer run-script post-install-cmd --no-interaction
RUN composer dump-autoload --optimize


FROM php:7-fpm-alpine

# Install Postgres and Zip tools and command line client
RUN apk add --no-cache autoconf g++ make \
    postgresql postgresql-client \
    libzip-dev unzip

# Install core PHP extensions
RUN docker-php-ext-install opcache zip

# Install Redis
RUN pecl install redis \
  && docker-php-ext-enable redis

# Install Xdebug if directed to with build arg from docker-compose.yml
ARG REQUIRE_XDEBUG=false
RUN if [ $REQUIRE_XDEBUG = "true" ] ; then \
        pecl install xdebug; \
        docker-php-ext-enable xdebug; \
    fi ;

# Add NGINX
RUN apk add --no-cache nginx

# Route NGINX logs to stdout/stderr
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
  && ln -sf /dev/stderr /var/log/nginx/error.log

# Install openssl for wget and certificate generation
RUN apk add --update openssl

# Add Confd to configure parameters on start
ENV CONFD_VERSION="0.16.0"
RUN wget -q -O /usr/local/bin/confd "https://github.com/kelseyhightower/confd/releases/download/v${CONFD_VERSION}/confd-${CONFD_VERSION}-linux-amd64" \
  && chmod +x /usr/local/bin/confd

# Add Waitforit to wait on API starting
ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
  && chmod +x /usr/local/bin/waitforit

WORKDIR /var/www

# Generate certificate
RUN mkdir -p /etc/nginx/certs
RUN openssl req -newkey rsa:4096 -x509 -nodes -keyout /etc/nginx/certs/app.key -new -out /etc/nginx/certs/app.crt -subj "/C=GB/ST=GB/L=London/O=OPG/OU=Digital/CN=default" -sha256 -days "3650"

EXPOSE 80
EXPOSE 443

WORKDIR /var/www
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
COPY --from=gulp /app/web/assets web/assets
COPY --from=gulp /app/web/images web/images
COPY --from=gulp /app/src/AppBundle/Resources/views/Css src/AppBundle/Resources/views/Css
COPY docker/confd /etc/confd
ENV TIMEOUT=60

RUN mkdir -p var/cache \
  && mkdir -p var/logs \
  && chown -R www-data var

CMD confd -onetime -backend env \
  && waitforit -address=$FRONTEND_API_URL/manage/availability -timeout=$TIMEOUT -insecure \
  && php-fpm -D \
  && nginx
