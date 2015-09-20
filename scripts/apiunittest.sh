#!/bin/bash
#let's configure environment
run-parts /etc/my_init.d

cd /app
# reset database and migrate
/sbin/setuser app php vendor/phpunit/phpunit/phpunit -c tests/phpunit/phpunit.xml