awslocal s3api create-bucket --bucket pa-uploads-local
awslocal ssm put-parameter --name "/default/flag/document-sync" --value "1" --type "String"
