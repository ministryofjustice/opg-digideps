#!/bin/bash

#$1=bucket_name
#$2=pipeline

usage ()
{
  echo "Usage : $0  <S3 bucket name> <pipeline>"
  exit
}

if [ "$#" -ne 2 ]
then
  usage
fi

db_archive=dd-$API_DATABASE_NAME-$(date +%Y%m%d).pg.gz

if (PGPASSWORD="$API_DATABASE_PASSWORD" pg_dump -C -h $API_DATABASE_HOSTNAME -U $API_DATABASE_USERNAME $API_DATABASE_NAME | gzip | aws s3 cp --region e
u-west-1 --sse aws:kms - s3://$1/backups/$2/$db_archive)
then
        echo "DB snapshot created copied to S3 bucket SUCCESSFULLY"
else
        echo "DB snapshot creation or upload to S3 backup bucket FAILED"
	usage
fi
