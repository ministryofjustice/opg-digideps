#!/bin/bash
# exit on error
set -e

php vendor/bin/phpunit -c tests/phpunit --coverage-php tests/phpunit/coverage/client-unit-tests.cov

php vendor/phpunit/phpcov/phpcov merge --clover "./tests/phpunit/coverage/client-unit-tests.xml" "./tests/phpunit/coverage"

php vendor/phpunit/phpcov/phpcov merge --html "./tests/phpunit/coverage/client-unit-tests" "./tests/phpunit/coverage"

python "import shutil; shutil.make_archive('client-unit-tests.html.zip', 'zip', './tests/phpunit/coverage/client-unit-tests')"
