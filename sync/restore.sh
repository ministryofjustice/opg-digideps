#! /bin/sh

set -e
set -o pipefail

if [ "${POSTGRES_DATABASE_DESTINATION}" = "**None**" ]; then
  echo "You need to set the POSTGRES_DATABASE_DESTINATION environment variable."
  exit 1
fi

if [ "${POSTGRES_HOST_DESTINATION}" = "**None**" ]; then
  if [ -n "${POSTGRES_PORT_5432_TCP_ADDR}" ]; then
    POSTGRES_HOST=$POSTGRES_PORT_5432_TCP_ADDR
    POSTGRES_PORT=$POSTGRES_PORT_5432_TCP_PORT
  else
    echo "You need to set the POSTGRES_HOST_DESTINATION environment variable."
    exit 1
  fi
fi

if [ "${POSTGRES_USER_DESTINATION}" = "**None**" ]; then
  echo "You need to set the POSTGRES_USER_DESTINATION environment variable."
  exit 1
fi

if [ "${POSTGRES_PASSWORD_DESTIONATION}" = "**None**" ]; then
  echo "You need to set the POSTGRES_PASSWORD_DESTIONATION environment variable or link to a container named POSTGRES."
  exit 1
fi


export PGPASSWORD=$POSTGRES_PASSWORD_DESTINATION
POSTGRES_HOST_OPTS_DESTINATION="-h $POSTGRES_HOST_DESTINATION -p $POSTGRES_PORT_DESTINATION -U $POSTGRES_USER_DESTINATION"

echo "Finding latest backup"

LATEST_BACKUP=$(aws s3 --endpoint-url $S3_ENDPOINT ls s3://$S3_BUCKET/$S3_PREFIX/ | sort | tail -n 1 | awk '{ print $4 }')

echo "Fetching ${LATEST_BACKUP} from S3"

aws s3 --endpoint-url $S3_ENDPOINT cp s3://$S3_BUCKET/$S3_PREFIX/${LATEST_BACKUP} dump.sql.gz
gzip -d dump.sql.gz

if [ "${DROP_PUBLIC}" == "yes" ]; then
	echo "Recreating the public schema"
	psql $POSTGRES_HOST_OPTS_DESTINATION -d $POSTGRES_DATABASE_DESTINATION -c "drop schema public cascade; create schema public;"
fi

echo "Restoring ${LATEST_BACKUP}"

psql $POSTGRES_HOST_OPTS_DESTINATION -d $POSTGRES_DATABASE_DESTINATION < dump.sql

echo "Restore complete"
