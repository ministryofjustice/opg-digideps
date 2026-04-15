FROM composer:2.9@sha256:97051b5ca53613b16f920a3ec970c6510113897a9a0e56cbbaf146ca75f60f4c

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
