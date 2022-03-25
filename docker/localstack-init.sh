#! /usr/bin/env sh

set -e

awslocal logs create-log-group --log-group-name audit-local

awslocal s3api create-bucket --bucket pa-uploads-local
awslocal s3api put-bucket-versioning --bucket pa-uploads-local --versioning-configuration Status=Enabled

awslocal ssm put-parameter --name "/default/flag/checklist-sync" --value "1" --type String --overwrite
awslocal ssm put-parameter --name "/default/flag/document-sync" --value "1" --type String --overwrite
awslocal ssm put-parameter --name "/default/flag/paper-reports" --value "0" --type String --overwrite

awslocal ssm put-parameter --name "/default/parameter/checklist-sync-row-limit" --value "100" --type String --overwrite
awslocal ssm put-parameter --name "/default/parameter/document-sync-interval-minutes" --value "4" --type String --overwrite
awslocal ssm put-parameter --name "/default/parameter/document-sync-row-limit" --value "100" --type String --overwrite

awslocal secretsmanager create-secret --name "default/opg-response-slack-token" --secret-string "IAMAFAKETOKEN"
