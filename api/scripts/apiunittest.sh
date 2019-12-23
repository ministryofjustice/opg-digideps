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
php bin/phpunit -c tests tests/AppBundle/Controller/ --coverage-php tests/coverage/Controller.cov
php bin/phpunit -c tests tests/AppBundle/ControllerReport/ --coverage-php tests/coverage/ControllerReport.cov
#php bin/phpunit -c tests tests/AppBundle/Controller-Ndr/ --coverage-php var/coverage/unit.cov
#php bin/phpunit -c tests tests/AppBundle/Entity/ --coverage-php var/coverage/unit.cov
#php bin/phpunit -c tests tests/AppBundle/Factory/ --coverage-php var/coverage/unit.cov
#php bin/phpunit -c tests tests/AppBundle/Security/ --coverage-php var/coverage/unit.cov
#php bin/phpunit -c tests tests/AppBundle/Service/ --coverage-php var/coverage/unit.cov
#php bin/phpunit -c tests tests/AppBundle/Stats/ --coverage-php var/coverage/unit.cov
#php bin/phpunit -c tests tests/AppBundle/Transformer/ --coverage-php var/coverage/unit.cov
#php bin/phpunit -c tests tests/AppBundle/v2/ --coverage-php var/coverage/unit.cov
