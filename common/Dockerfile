FROM composer:2.9

WORKDIR /common
COPY composer.json .
COPY composer.lock .
COPY phpunit.xml .
COPY phpstan.neon .
COPY src src
COPY tests tests

ENV COMPOSER_ROOT_VERSION="1.0.0"
RUN composer install

ENTRYPOINT ["composer", "run"]
