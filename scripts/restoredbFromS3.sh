#!/bin/bash
#$1=bucket name
#$2=snapshot name

usage ()
{
  echo "Usage : $0  <S3 bucket name> <snapshot name>"
  exit
}

if [ "$#" -ne 2 ]
then
  usage
fi

# Shutdown existing instance, drop and create DB.
echo "Restoring saved database snapshot s3://$1/$2..."
echo "Creating a blank Postgres DB...."

PGPASSWORD=${API_DATABASE_PASSWORD:=api} psql -P pager=off -U ${API_DATABASE_USERNAME:=api} -h ${API_DATABASE_HOSTNAME:=postgres} -p ${API_DATABASE_PORT:=5432} -c "SELECT pg_terminate_backend(pg_stat_activity.pid) FROM pg_stat_activity WHERE pg_stat_activity.datname = '${API_DATABASE_NAME:=api}' AND pid <> pg_backend_pid();"
PGPASSWORD=${API_DATABASE_PASSWORD:=api} dropdb -U ${API_DATABASE_USERNAME:=api} -h ${API_DATABASE_HOSTNAME:=postgres} -p ${API_DATABASE_PORT:=5432} ${API_DATABASE_NAME:=api}
PGPASSWORD=${API_DATABASE_PASSWORD:=api} createdb -U ${API_DATABASE_USERNAME:=api} -h ${API_DATABASE_HOSTNAME:=postgres} -p ${API_DATABASE_PORT:=5432} ${API_DATABASE_NAME:=api}

# Load snapshot into new DB instance.
echo "Restoring database tables and data...."

if (aws s3 cp --sse aws:kms --region eu-west-1 s3://$1/$2 - | gunzip  | PGPASSWORD=${API_DATABASE_PASSWORD:=api} psql -P pager=off -U ${API_DATABASE_USERNAME:=api} -h ${API_DATABASE_HOSTNAME:=postgres} -p ${API_DATABASE_PORT:=5432} ${API_DATABASE_NAME:=api})
then
        echo "Restore from specified snapshot s3://$1/$2 SUCCESSFUL!"
else
        echo "DB restore from specified snapshot  s3://$1/$2 FAILED!"
	usage
fi
