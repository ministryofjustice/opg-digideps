#!/usr/bin/env bash
set -e

environment=${1:-development}

export PGHOST=${DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${DATABASE_PASSWORD:=api}
export PGDATABASE=${DATABASE_NAME:=api}
export PGUSER=${DATABASE_USERNAME:=api}
export SSL=${DATABASE_SSL:=allow}

#Apply migrations to rebuild database
php app/console doctrine:database:drop --force --if-exists --connection=migrations
php app/console doctrine:database:create --connection=migrations
php app/console doctrine:migrations:status
php app/console doctrine:migrations:migrate --no-interaction -vvv

if [ "$environment" == "local" ]; then
    php app/console doctrine:database:drop --force --if-exists --connection=migrations --env=test
    php app/console doctrine:database:create --connection=migrations --env=test
    php app/console doctrine:migrations:status --env=test
    php app/console doctrine:migrations:migrate --no-interaction -vvv --env=test
fi
