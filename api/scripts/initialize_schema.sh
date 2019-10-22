#!/usr/bin/env bash
set -e

export PGHOST=${DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${DATABASE_PASSWORD:=api}
export PGDATABASE=${DATABASE_NAME:=api}
export PGUSER=${DATABASE_USERNAME:=api}

echo "Dropping $PGDATABASE database, user $PGUSER on $PGHOST"

psql -c "DROP SCHEMA IF EXISTS public CASCADE; CREATE SCHEMA IF NOT EXISTS public;"
