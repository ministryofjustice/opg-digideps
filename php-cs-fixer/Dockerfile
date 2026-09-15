FROM php:8.4-cli-alpine

RUN apk add --no-cache curl

RUN curl -L \
    https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/releases/download/v3.95.11/php-cs-fixer.phar \
    -o /usr/local/bin/php-cs-fixer \
 && chmod +x /usr/local/bin/php-cs-fixer

WORKDIR /workdir

ENTRYPOINT ["php", "/usr/local/bin/php-cs-fixer"]
