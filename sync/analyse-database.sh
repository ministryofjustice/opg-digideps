#! /usr/bin/env sh

set -e
set -o pipefail

# source common.sh

export PGPASSWORD=$POSTGRES_PASSWORD
POSTGRES_HOST_OPTS="-h $POSTGRES_HOST -p $POSTGRES_PORT -U $POSTGRES_USER"

echo "Running Analyze"
if psql $POSTGRES_HOST_OPTS -d $POSTGRES_DATABASE -c "ANALYZE VERBOSE;"; then
    echo "analyze_database - success - analyze command ran successfully against ${POSTGRES_HOST}"
else
    echo "analyze_database - failure - analyze command failed against ${POSTGRES_HOST}"
fi
