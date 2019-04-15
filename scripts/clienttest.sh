#!/bin/bash
set -e
#let's configure environment
run-parts /etc/my_init.d
/sbin/setuser app mkdir -p /tmp/behat
chown app:app /tmp/behat
chown app:app /tmp

# create log dir locally failing sometimes)
mkdir -p /var/log/app
chown app:app /var/log/app

cd /app
export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=api}
export PGUSER=${API_DATABASE_USERNAME:=api}
rm -rf app/cache/*

# phpunit
/sbin/setuser app php vendor/phpunit/phpunit/phpunit -c tests/phpunit/

# behat
/sbin/setuser app bin/behat --config=tests/behat/behat.yml --profile=${PROFILE:=headless} --stop-on-failure
