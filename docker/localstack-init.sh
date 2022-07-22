set -e

groupExists=$( (awslocal logs describe-log-groups | jq '.logGroups[] | select(.logGroupName == "audit-local")') )

#Create log group if it does not exist (stops ResourceAlreadyExists errors)
if [ -z "$groupExists" ]
then
    awslocal logs create-log-group --log-group-name audit-local
fi

awslocal s3api create-bucket --bucket pa-uploads-local
awslocal s3api put-bucket-versioning --bucket pa-uploads-local --versioning-configuration Status=Enabled

awslocal ssm put-parameter --name "/default/flag/checklist-sync" --value "1" --type String --overwrite
awslocal ssm put-parameter --name "/default/flag/document-sync" --value "1" --type String --overwrite
awslocal ssm put-parameter --name "/default/flag/paper-reports" --value "0" --type String --overwrite

awslocal ssm put-parameter --name "/default/parameter/checklist-sync-row-limit" --value "100" --type String --overwrite
awslocal ssm put-parameter --name "/default/parameter/document-sync-interval-minutes" --value "4" --type String --overwrite
awslocal ssm put-parameter --name "/default/parameter/document-sync-row-limit" --value "100" --type String --overwrite

awslocal secretsmanager create-secret --name "default/opg-response-slack-token" --secret-string "IAMAFAKETOKEN"

openssl genrsa -out private.pem 2048
openssl rsa -in private.pem -outform PEM -pubout -out public.pem

awslocal secretsmanager create-secret --name "default/private-jwt-key-base64" --secret-string "$(base64 private.pem)"
awslocal secretsmanager create-secret --name "default/public-jwt-key-base64" --secret-string "$(base64 public.pem)"

rm private.pem public.pem
