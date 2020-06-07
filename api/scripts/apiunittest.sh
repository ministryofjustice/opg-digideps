#!/bin/bash
# exit on error
set -e
echo `id`
echo `id -u var-www`

echo 'Starting confd...........'
# Generate config files so test bootstrap can address the DB
confd -onetime -backend env

echo 'Finished confd...........'
# Export unit test DB config so it can be used in tests
export PGHOST=${DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${DATABASE_PASSWORD:=api}
export PGDATABASE=${DATABASE_NAME:=digideps_unit_test}
export PGUSER=${DATABASE_USERNAME:=api}

# Run each folder of unit tests individually. If we were to run them all
# individually it would cause a memory leak.
php bin/phpunit -c tests tests/AppBundle/Command/ --coverage-php tests/coverage/Command.cov
php bin/phpunit -c tests tests/AppBundle/Controller/ --coverage-php tests/coverage/Controller.cov
php bin/phpunit -c tests tests/AppBundle/ControllerReport/ --coverage-php tests/coverage/ControllerReport.cov
php bin/phpunit -c tests tests/AppBundle/Controller-Ndr/ --coverage-php tests/coverage/Controller-Ndr.cov
php bin/phpunit -c tests tests/AppBundle/Entity/ --coverage-php tests/coverage/Entity.cov
php bin/phpunit -c tests tests/AppBundle/Factory/ --coverage-php tests/coverage/Factory.cov
php bin/phpunit -c tests tests/AppBundle/Security/ --coverage-php tests/coverage/Security.cov
php bin/phpunit -c tests tests/AppBundle/Service/ --coverage-php tests/coverage/Service.cov
php bin/phpunit -c tests tests/AppBundle/Stats/ --coverage-php tests/coverage/Stats.cov
php bin/phpunit -c tests tests/AppBundle/Transformer/ --coverage-php tests/coverage/Transformer.cov
php bin/phpunit -c tests tests/AppBundle/v2/ --coverage-php tests/coverage/v2.cov

php vendor/phpunit/phpcov/phpcov merge --clover "tests/coverage/merged.xml" "tests/coverage"
