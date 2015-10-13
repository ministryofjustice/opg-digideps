#!/bin/bash
set -e
#let's configure environment
run-parts /etc/my_init.d
chown app:app /tmp/behat

cd /app
/sbin/setuser app mkdir -p /tmp/behat
export PGHOST=postgres
export PGPASSWORD=api
export PGDATABASE=api
export PGUSER=api
rm -rf app/cache/*
suitename=${1:-deputy}

/sbin/setuser app php vendor/phpunit/phpunit/phpunit -c tests/phpunit/

# tests with coverage (install php5-xdebug if needed)
#/sbin/setuser app php -d zend_extension=xdebug.so bin/phpunit -c tests/phpunit/phpunit.xml --coverage-html=web/coverage-html

if [ -f tests/behat/behat.yml ]; then
    behatConfigFile=tests/behat/behat.yml
else
    behatConfigFile=tests/behat/behat.yml.dist
fi
/sbin/setuser app bin/behat --config=$behatConfigFile --suite=$suitename --stop-on-failure
