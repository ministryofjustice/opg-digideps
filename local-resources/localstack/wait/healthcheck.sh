#!/usr/bin/env bash

# S3
buckets=$(awslocal s3 ls)

expected_buckets=("pa-uploads-local" "sirius-bucket-local" "opg-performance-data")
for bucket in ${expected_buckets[@]} ; do
	echo "Checking for $bucket"
	if [[ $buckets != *"$bucket"* ]]; then
		echo "$bucket was not found"
        exit 1
    fi
done

echo "Health checks completed successfully"
exit 0
