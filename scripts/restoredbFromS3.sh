#!/bin/bash
#$1=bucket name
#$2=pipeline
#$3=archive name

usage ()
{
  echo "Usage : $0  <S3 bucket name> <pipeline> <snapshot name>"
  exit
}

if [ "$#" -ne 3 ]
then
  usage
fi

# Shutdown existing instance, drop and create DB.
echo "Restoring saved database snapshot s3://$1/backups/$2/$3..."
echo "Creating a blank Postgres DB...."

PGPASSWORD=$API_DATABASE_PASSWORD psql -P pager=off -U $API_DATABASE_USERNAME -h $API_DATABASE_HOSTNAME -p $API_DATABASE_PORT -c "SELECT pg_terminate_b
ackend(pg_stat_activity.pid) FROM pg_stat_activity WHERE pg_stat_activity.datname = '$API_DATABASE_NAME' AND pid <> pg_backend_pid();"
PGPASSWORD=$API_DATABASE_PASSWORD dropdb -U $API_DATABASE_USERNAME -h $API_DATABASE_HOSTNAME -p $API_DATABASE_PORT $API_DATABASE_NAME
PGPASSWORD=$API_DATABASE_PASSWORD createdb -U $API_DATABASE_USERNAME -h $API_DATABASE_HOSTNAME -p $API_DATABASE_PORT $API_DATABASE_NAME

# Load snapshot into new DB instance.
echo "Restoring database tables and data...."

if (aws s3 cp --sse aws:kms --region eu-west-1 s3://$1/backups/$2/$3 - | gunzip  | PGPASSWORD=$API_DATABASE_PASSWORD psql -P pager=off -U $API_DATABASE_USERNAME -h $API_DATABASE_HOSTNAME -p $API_DATABASE_PORT $API_DATABASE_NAME)
then
        echo "Restore from specified snapshot SUCCESSFUL!"
else
        echo "DB restore from specified snapshot FAILED!"
	usage
fi
