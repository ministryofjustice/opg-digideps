awslocal s3api create-bucket --bucket pa-uploads-local
awslocal s3api put-bucket-versioning --bucket pa-uploads-local --versioning-configuration Status=Enabled
awslocal ssm put-parameter --name "/default/flag/document-sync" --value "1" --type String
awslocal ssm put-parameter --name "/default/parameter/document-sync-row-limit" --value "100" --type String
awslocal logs create-log-group --log-group-name audit-local
