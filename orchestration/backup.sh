#! /usr/bin/env sh

set -e
set -o pipefail

source common.sh

while true; do
    echo "database_activity_in_progress activity=backup"
    sleep 45
done &
heartbeat_pid=$!

echo "Creating dump of ${POSTGRES_DATABASE} database from ${POSTGRES_HOST}..."

pg_dump $POSTGRES_HOST_OPTS $POSTGRES_DATABASE --no-owner | gzip > dump.sql.gz

echo "Uploading dump to $S3_BUCKET"

FILE_NAME=${POSTGRES_DATABASE}_$(date +"%Y-%m-%dT%H:%M:%SZ").sql.gz

cat dump.sql.gz | aws $AWS_ARGS s3 cp $S3_OPTS - s3://$S3_BUCKET/$S3_PREFIX/${FILE_NAME} || exit 2

echo "SQL backup uploaded successfully"

kill "$heartbeat_pid"
