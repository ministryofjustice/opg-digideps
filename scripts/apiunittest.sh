#!/bin/bash
set -e
#let's configure environment
run-parts /etc/my_init.d

cd /app
# clear cache
rm -rf app/cache/*
# run source code tests
/sbin/setuser app php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml
# /sbin/setuser app php -d zend_extension=xdebug.so vendor/phpunit/phpunit/phpunit -c tests/phpunit/src/phpunit.xml --coverage-clover=build/coverage.xml --coverage-html=build/coverage-html
# run data migration tests (only enable when new data migrations to test are added)
# /sbin/setuser app php vendor/phpunit/phpunit/phpunit -c tests/phpunit/DataMigration/phpunit.xml
