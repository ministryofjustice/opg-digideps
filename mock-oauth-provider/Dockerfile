FROM php:8.3-fpm-alpine

RUN apk --no-cache add nginx
RUN apk upgrade && \
  rm -rf /var/cache/apk/*

# Patch Vulnerable Packages
RUN apk upgrade --no-cache busybox curl libcurl libxml2 libxslt pcre2 xz xz-libs

COPY nginx.conf /etc/nginx/nginx.conf

COPY index.php /var/www/html/index.php
COPY login.html /var/www/html/login.html
COPY style.css /var/www/html/style.css

CMD php-fpm -D && nginx -g "daemon off;"
