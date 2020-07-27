#!/usr/bin/env bash
set -e

export PGHOST=${DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${DATABASE_PASSWORD:=api}
export PGDATABASE=${DATABASE_NAME:=api}
export PGUSER=${DATABASE_USERNAME:=api}

# We need below to create the params file on container start
confd -onetime -backend env

su-exec www-data php app/console doctrine:fixtures:load --no-interaction
