#! /usr/bin/env sh

set -e
set -o pipefail

# source common.sh

export PGPASSWORD=$POSTGRES_PASSWORD
POSTGRES_HOST_OPTS="-h $POSTGRES_HOST -p $POSTGRES_PORT -U $POSTGRES_USER"

echo "Running Analyse"
psql $POSTGRES_HOST_OPTS -d $POSTGRES_DATABASE -c "ANALYSE VERBOSE;"
