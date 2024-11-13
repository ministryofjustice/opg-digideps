#!/usr/bin/env bash
set -e

environment=${1:-development}

export PGHOST=${DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${DATABASE_PASSWORD:=api}
export PGDATABASE=${DATABASE_NAME:=api}
export PGUSER=${DATABASE_USERNAME:=api}
export SSL=${DATABASE_SSL:=allow}

# We need below to create the params file on container start
confd -onetime -backend env

#Apply migrations to rebuild database
php app/console doctrine:database:drop --force --if-exists
php app/console doctrine:database:create
php app/console doctrine:migrations:status
php app/console doctrine:migrations:migrate --no-interaction -vvv

if [ "$environment" == "local" ]; then
    php app/console doctrine:database:drop --force --if-exists --env=test
    php app/console doctrine:database:create --env=test
    php app/console doctrine:migrations:status --env=test
    php app/console doctrine:migrations:migrate --no-interaction -vvv --env=test
fi
