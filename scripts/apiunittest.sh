#!/bin/bash
set -e
#let's configure environment
run-parts /etc/my_init.d

export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=digideps_unit_test}
export PGUSER=${API_DATABASE_USERNAME:=api}

cd /app
# clear cache
rm -rf app/cache/*

rm -f /tmp/dd_stats.csv
/sbin/setuser app php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Controller/
/sbin/setuser app php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Service/
/sbin/setuser app php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Entity/
