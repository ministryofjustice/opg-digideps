#!/bin/bash
set -e
#let's configure environment
confd -onetime -backend env

export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=digideps_unit_test}
export PGUSER=${API_DATABASE_USERNAME:=api}

cd /var/www
su-exec www-data php app/console doctrine:query:sql "select pg_terminate_backend(pid) from pg_stat_activity where datname='digideps_unit_test'"
su-exec www-data php app/console doctrine:query:sql "DROP DATABASE IF EXISTS digideps_unit_test;"
su-exec www-data php app/console doctrine:query:sql "CREATE DATABASE digideps_unit_test;"
