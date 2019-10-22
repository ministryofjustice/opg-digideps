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
php bin/phpunit -c tests tests/AppBundle/Controller/
php bin/phpunit -c tests tests/AppBundle/Controller-Report/
php bin/phpunit -c tests tests/AppBundle/Controller-Ndr/
php bin/phpunit -c tests tests/AppBundle/Entity/
php bin/phpunit -c tests tests/AppBundle/Factory/
php bin/phpunit -c tests tests/AppBundle/Security/
php bin/phpunit -c tests tests/AppBundle/Service/
php bin/phpunit -c tests tests/AppBundle/Stats/
php bin/phpunit -c tests tests/AppBundle/Transformer/
php bin/phpunit -c tests tests/AppBundle/v2/
