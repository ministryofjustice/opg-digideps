#!/bin/bash
# usage: api_unit_test.sh <cov-html (optional)>
# if cov-html is supplied as an argument, will generate HTML coverage reports
# exit on error
set -e

php vendor/bin/phpunit -c tests/Unit tests/Unit/ --coverage-php tests/coverage/api-unit.cov

case "$1" in
    cov-html)
        # generate HTML coverage report
        php -d memory_limit=256M vendor/phpunit/phpcov/phpcov merge --html "./build/coverage-api" "./tests/coverage"
        ;;
esac

php vendor/phpunit/phpcov/phpcov merge --clover "./tests/coverage/api-unit-tests.xml" "./tests/coverage"
