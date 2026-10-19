FROM php:8.4.26RC1-cli-alpine3.24@sha256:2833e4b9335af7fcb1b25bee3b4bb336406348f8d979f74441cc10031b871550

RUN apk add --no-cache curl

RUN curl -L \
    https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/releases/download/v3.95.11/php-cs-fixer.phar \
    -o /usr/local/bin/php-cs-fixer \
 && chmod +x /usr/local/bin/php-cs-fixer

WORKDIR /workdir

ENTRYPOINT ["php", "/usr/local/bin/php-cs-fixer"]
