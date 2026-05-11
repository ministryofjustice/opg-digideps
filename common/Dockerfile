FROM composer:2.9@sha256:d4ee82e9374726e237781aa32837a9933e288ca9ac11d30e6df3d9f27b174726

WORKDIR /common
COPY composer.json .
COPY composer.lock .
COPY phpunit.xml .
COPY phpstan.neon .
COPY phpstan-fragment.neon .
COPY src src
COPY tests tests

ENV COMPOSER_ROOT_VERSION="1.0.0"
RUN composer install

ENTRYPOINT ["composer", "run"]
