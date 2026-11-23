FROM php:8.4.28-alpine3.24@sha256:5b84d9d7524ac30fe65dc50de6117efa54b777c38b4f3d9060d7f2dfe070bf9c

# TODO Remove as temporary workaround until Alpine ships the patched package
RUN apk update && apk add --upgrade "zlib==1.4-r0"

RUN apk add --no-cache curl

RUN curl -L \
    https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/releases/download/v3.95.11/php-cs-fixer.phar \
    -o /usr/local/bin/php-cs-fixer \
 && chmod +x /usr/local/bin/php-cs-fixer

WORKDIR /workdir

ENTRYPOINT ["php", "/usr/local/bin/php-cs-fixer"]
