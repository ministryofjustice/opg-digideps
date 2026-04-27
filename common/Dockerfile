FROM composer:2.9@sha256:dc292c5c0f95f526b051d4c341bf08e7e2b18504c74625e3203d7f123050e318

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
