#! /bin/sh

set -e
set -o pipefail

if [ "${POSTGRES_DATABASE_SOURCE}" = "**None**" ]; then
  echo "You need to set the POSTGRES_DATABASE_SOURCE environment variable."
  exit 1
fi

if [ "${POSTGRES_HOST_SOURCE}" = "**None**" ]; then
  if [ -n "${POSTGRES_PORT_5432_TCP_ADDR}" ]; then
    POSTGRES_HOST=$POSTGRES_PORT_5432_TCP_ADDR
    POSTGRES_PORT=$POSTGRES_PORT_5432_TCP_PORT
  else
    echo "You need to set the POSTGRES_HOST_SOURCE environment variable."
    exit 1
  fi
fi

if [ "${POSTGRES_USER_SOURCE}" = "**None**" ]; then
  echo "You need to set the POSTGRES_USER_SOURCE environment variable."
  exit 1
fi

if [ "${POSTGRES_PASSWORD_SOURCE}" = "**None**" ]; then
  echo "You need to set the POSTGRES_PASSWORD_SOURCE environment variable or link to a container named POSTGRES."
  exit 1
fi

export PGPASSWORD=$POSTGRES_PASSWORD_SOURCE
POSTGRES_HOST_OPTS_SOURCE="-h $POSTGRES_HOST_SOURCE -p $POSTGRES_PORT_SOURCE -U $POSTGRES_USER_SOURCE $POSTGRES_EXTRA_OPTS"

echo "Creating dump of ${POSTGRES_DATABASE_SOURCE} database from ${POSTGRES_HOST_SOURCE}..."

pg_dump $POSTGRES_HOST_OPTS_SOURCE $POSTGRES_DATABASE_SOURCE | gzip > dump.sql.gz

echo "Uploading dump to $S3_BUCKET"

if [ "${S3_S3V4}" = "yes" ]; then
    aws configure set default.s3.signature_version s3v4
fi

FILE_NAME=${POSTGRES_DATABASE_SOURCE}_$(date +"%Y-%m-%dT%H:%M:%SZ").sql.gz

cat dump.sql.gz | aws --endpoint-url $S3_ENDPOINT s3 cp - s3://$S3_BUCKET/$S3_PREFIX/${FILE_NAME} || exit 2

echo "SQL backup uploaded successfully"
