#!/bin/bash
#let's configure environment
run-parts /etc/my_init.d

cd /app
# reset database and migrate
/sbin/setuser app php app/console doctrine:query:sql "DROP SCHEMA IF EXISTS public cascade; CREATE SCHEMA IF NOT EXISTS public;" --env=test
/sbin/setuser app php app/console doctrine:migrations:migrate --no-interaction --env=test
/sbin/setuser app php vendor/phpunit/phpunit/phpunit