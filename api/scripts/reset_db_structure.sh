#!/usr/bin/env bash
set -e

export PGHOST=${DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${DATABASE_PASSWORD:=api}
export PGDATABASE=${DATABASE_NAME:=api}
export PGUSER=${DATABASE_USERNAME:=api}
export SSL=${DATABASE_SSL:=allow}

# We need below to create the params file on container start
confd -onetime -backend env

#Apply migrations to rebuild database
su-exec www-data php app/console doctrine:database:drop --force --if-exists
su-exec www-data php app/console doctrine:database:create
su-exec www-data php app/console doctrine:migrations:status
su-exec www-data php app/console doctrine:migrations:migrate --no-interaction -vvv
