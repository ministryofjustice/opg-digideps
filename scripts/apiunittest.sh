#!/bin/bash
#let's configure environment
run-parts /etc/my_init.d

cd /app
/sbin/setuser app php -d zend_extension=xdebug.so vendor/phpunit/phpunit/phpunit -c tests/phpunit/phpunit.xml --debug --coverage-clover=build/coverage.xml --coverage-html=build/coverage-html
