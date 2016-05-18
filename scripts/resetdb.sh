#!/bin/bash
set -e
#let's configure environment
run-parts /etc/my_init.d

cd /app
export PGPASSWORD=$API_DATABASE_PASSWORD
export PGHOST=$API_DATABASE_HOSTNAME
export PGPORT=$API_DATABASE_PORT
export PGDATABASE=$API_DATABASE_NAME 
export PGUSER=$API_DATABASE_USERNAME
/sbin/setuser app  psql -c "DROP SCHEMA IF EXISTS public CASCADE; CREATE SCHEMA IF NOT EXISTS public;"
/sbin/setuser app php app/console doctrine:migrations:status-check
/sbin/setuser app php app/console doctrine:migrations:migrate --no-interaction -vvv
/sbin/setuser app php app/console digideps:fixtures
