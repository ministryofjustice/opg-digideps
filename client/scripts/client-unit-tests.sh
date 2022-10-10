#!/bin/bash
# exit on error
set -e

php vendor/bin/phpunit -c tests/phpunit --coverage-php tests/phpunit/coverage/client-unit-tests.cov --verbose

php vendor/phpunit/phpcov/phpcov merge --clover "./tests/phpunit/coverage/client-unit-tests.xml" "./tests/phpunit/coverage"
