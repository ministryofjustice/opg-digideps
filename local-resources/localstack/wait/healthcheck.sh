#!/usr/bin/env bash

# S3
buckets=$(awslocal s3 ls)

echo $buckets | grep "pa-uploads-local" || exit 1
echo $buckets | grep "sirius-bucket-local" || exit 1
echo $buckets | grep "opg-performance-data" || exit 1
