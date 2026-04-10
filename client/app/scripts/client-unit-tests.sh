#!/bin/bash
# exit on error
set -e

php vendor/bin/phpunit -c tests/Unit --coverage-php tests/Unit/coverage/client-unit-tests.cov

case "$1" in
    cov-html)
        # generate HTML coverage report
        php -d memory_limit=256M vendor/phpunit/phpcov/phpcov merge --html "./build/coverage-client" "./tests/Unit/coverage"
        ;;
esac

php vendor/phpunit/phpcov/phpcov merge --clover "./tests/Unit/coverage/client-unit-tests.xml" "./tests/Unit/coverage"
