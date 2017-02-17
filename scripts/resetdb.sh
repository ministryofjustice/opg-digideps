#!/bin/bash
set -e
# We need below to create the params file on container start
run-parts /etc/my_init.d

cd /app
export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=api}
export PGUSER=${API_DATABASE_USERNAME:=api}

echo "Dropping $PGDATABASE database, user $PGUSER on $PGHOST"

/sbin/setuser app  psql -c "DROP SCHEMA IF EXISTS public CASCADE; CREATE SCHEMA IF NOT EXISTS public;"
/sbin/setuser app php app/console doctrine:migrations:status-check
/sbin/setuser app php app/console doctrine:migrations:migrate --no-interaction -vvv
/sbin/setuser app php app/console digideps:fixtures
