#!/bin/bash
#let's configure environment
run-parts /etc/my_init.d

cd /app
# reset database and migrate
/sbin/setuser app php vendor/phpunit/phpunit/phpunit -c tests/phpunit/phpunit.xml

# tests with coverage (install php5-xdebug if needed)
#/sbin/setuser app php -d zend_extension=xdebug.so vendor/phpunit/phpunit/phpunit -c tests/phpunit/phpunit.xml --coverage-html=web/coverage-html
