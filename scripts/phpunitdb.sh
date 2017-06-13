#!/bin/bash
set -e
#let's configure environment
run-parts /etc/my_init.d

export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=digideps_unit_test}
export PGUSER=${API_DATABASE_USERNAME:=api}

cd /app
/sbin/setuser app php app/console doctrine:query:sql "select pg_terminate_backend(pid) from pg_stat_activity where datname='digideps_unit_test'"
/sbin/setuser app php app/console doctrine:query:sql "DROP DATABASE IF EXISTS digideps_unit_test;"
/sbin/setuser app php app/console doctrine:query:sql "CREATE DATABASE digideps_unit_test;"
