FROM composer:2.9@sha256:b148074c5cf8e5c564e92baa3f0d2e28ffa0361ede57a03de3bf4b9cf80de54a

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
