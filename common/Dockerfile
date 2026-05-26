FROM composer:2.9@sha256:02062f7719ec9433a9d4256cfba1c792db96dc9db60a4a92e48264c9e166b877

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
