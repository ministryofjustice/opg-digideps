#!/bin/bash
# exit on error
set -e

php vendor/bin/phpunit -c tests/phpunit --coverage-php tests/phpunit/coverage/client-unit-tests.cov

case "$1" in
    cov-html)
        # generate HTML coverage report
        php -d memory_limit=256M vendor/phpunit/phpcov/phpcov merge --html "./build/coverage-client" "./tests/phpunit/coverage"
        ;;
esac

php vendor/phpunit/phpcov/phpcov merge --clover "./tests/phpunit/coverage/client-unit-tests.xml" "./tests/phpunit/coverage"
