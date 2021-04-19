#!/bin/bash
# exit on error
set -e

# Generate config files so test bootstrap can address the DB
confd -onetime -backend env

# Export unit test DB config so it can be used in tests
export PGHOST=${DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${DATABASE_PASSWORD:=api}
export PGDATABASE=${DATABASE_NAME:=digideps_unit_test}
export PGUSER=${DATABASE_USERNAME:=api}

# Run each folder of unit tests individually. If we were to run them all
# individually it would cause a memory leak.
php vendor/bin/phpunit -c tests tests/App/Command/ --coverage-php tests/coverage/Command.cov
php vendor/bin/phpunit -c tests tests/App/Controller/ --coverage-php tests/coverage/Controller.cov
php vendor/bin/phpunit -c tests tests/App/ControllerReport/ --coverage-php tests/coverage/ControllerReport.cov
php vendor/bin/phpunit -c tests tests/App/Controller-Ndr/ --coverage-php tests/coverage/Controller-Ndr.cov
php vendor/bin/phpunit -c tests tests/App/Entity/ --coverage-php tests/coverage/Entity.cov
php vendor/bin/phpunit -c tests tests/App/Factory/ --coverage-php tests/coverage/Factory.cov
php vendor/bin/phpunit -c tests tests/App/Security/ --coverage-php tests/coverage/Security.cov
php vendor/bin/phpunit -c tests tests/App/Service/ --coverage-php tests/coverage/Service.cov
php vendor/bin/phpunit -c tests tests/App/Stats/ --coverage-php tests/coverage/Stats.cov
php vendor/bin/phpunit -c tests tests/App/Transformer/ --coverage-php tests/coverage/Transformer.cov
php vendor/bin/phpunit -c tests tests/App/v2/ --coverage-php tests/coverage/v2.cov

php vendor/phpunit/phpcov/phpcov merge --clover "./tests/coverage/api-unit-tests.xml" "./tests/coverage"
