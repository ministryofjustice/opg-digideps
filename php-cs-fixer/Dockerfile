FROM php:8.4.27-alpine3.24@sha256:8d29b132476e5f54633d30bfbfa0b0b7c95a9cb0c308b3175d61351aea73b643

# TODO Remove as temporary workaround until Alpine ships the patched package
RUN apk update && apk add --upgrade "zlib==1.4-r0"

RUN apk add --no-cache curl

RUN curl -L \
    https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/releases/download/v3.95.11/php-cs-fixer.phar \
    -o /usr/local/bin/php-cs-fixer \
 && chmod +x /usr/local/bin/php-cs-fixer

WORKDIR /workdir

ENTRYPOINT ["php", "/usr/local/bin/php-cs-fixer"]
