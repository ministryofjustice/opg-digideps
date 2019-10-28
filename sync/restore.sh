#! /usr/bin/env sh

set -e
set -o pipefail

source common.sh

echo "Finding latest backup"

LATEST_BACKUP=$(aws s3 $AWS_ARGS ls s3://$S3_BUCKET/$S3_PREFIX/ | sort | tail -n 1 | awk '{ print $4 }')

echo "Fetching ${LATEST_BACKUP} from S3"

aws s3 $AWS_ARGS cp s3://$S3_BUCKET/$S3_PREFIX/${LATEST_BACKUP} dump.sql.gz

gzip -d dump.sql.gz

if [ "${DROP_PUBLIC}" == "yes" ]; then
	echo "Recreating the public schema"
	psql $POSTGRES_HOST_OPTS -d $POSTGRES_DATABASE -c "drop schema public cascade; create schema public;"
fi

echo "Restoring ${LATEST_BACKUP}"

psql $POSTGRES_HOST_OPTS -d $POSTGRES_DATABASE < dump.sql

echo "Restore complete"
