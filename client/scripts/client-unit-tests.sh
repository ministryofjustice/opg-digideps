#!/bin/bash
# exit on error
set -e
php vendor/bin/phpunit -c tests/phpunit --coverage-php tests/phpunit/coverage/client-unit-tests-service.cov
#php vendor/bin/phpunit -c tests/phpunit --testsuite "Client unit tests service files" --coverage-php tests/phpunit/coverage/client-unit-tests-service.cov
#php vendor/bin/phpunit -c tests/phpunit --testsuite "Client unit tests service directories 1" --coverage-php tests/phpunit/coverage/client-unit-tests-service.cov
#php vendor/bin/phpunit -c tests/phpunit --testsuite "Client unit tests service directories 2" --coverage-php tests/phpunit/coverage/client-unit-tests-service.cov
#php vendor/bin/phpunit -c tests/phpunit --testsuite "Client unit tests" --coverage-php tests/phpunit/coverage/client-unit-tests.cov
#php vendor/bin/phpunit -c tests/phpunit --testsuite "Pact contract tests" --coverage-php tests/phpunit/coverage/pact-contract-tests.cov

php vendor/phpunit/phpcov/phpcov merge --clover "./tests/phpunit/coverage/client-unit-tests.xml" "./tests/phpunit/coverage"
