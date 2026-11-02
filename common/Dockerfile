FROM composer:2.2.30@sha256:db5a72680c556826fdf498dc16e67b55c10da56928de06929cbc431b81271efb

WORKDIR /common
COPY composer.json .
COPY phpunit.xml .
COPY phpstan.neon .
COPY phpstan-fragment.neon .
COPY src src
COPY tests tests

ENV COMPOSER_ROOT_VERSION="1.0.0"
RUN composer install

ENTRYPOINT ["composer", "run"]
