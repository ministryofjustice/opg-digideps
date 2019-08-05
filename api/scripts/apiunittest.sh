#!/bin/bash
set -e
confd -onetime -backend env

export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=digideps_unit_test}
export PGUSER=${API_DATABASE_USERNAME:=api}

cd /var/www
# clear cache
rm -rf var/cache/*

rm -f /tmp/dd_stats.csv
rm -f /tmp/dd_stats.unittest.csv

su-exec www-data php app/console doctrine:migrations:status-check
su-exec www-data php app/console doctrine:migrations:migrate-lock --no-interaction --verbose

php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Controller/
php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Controller-Report/
php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Controller-Ndr/
php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Service/
php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Entity/
php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Transformer/
