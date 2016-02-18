#! /bin/bash

#$1=bucket_name
#$2=pipeline

db_archive=dd-$API_DATABASE_NAME-$(date +%Y%m%d).pg.gz

if (PGPASSWORD="$API_DATABASE_PASSWORD" pg_dump -C -h $API_DATABASE_HOSTNAME -U $API_DATABASE_USERNAME $API_DATABASE_NAME | gzip | aws s3 cp --region eu-west-1 --sse aws:kms - s3://$1/backups/$2/$
db_archive)
then
        echo "DB snapshot created copied to S3 bucket SUCCESSFULLY"
else
        echo "DB snapshot creation or upload to S3 backup bucket FAILED"
fi
