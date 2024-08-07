set -e

groupExists=$( (awslocal logs describe-log-groups | jq '.logGroups[] | select(.logGroupName == "audit-local")') )

#Create log group if it does not exist (stops ResourceAlreadyExists errors)
if [ -z "$groupExists" ]
then
    awslocal logs create-log-group --log-group-name audit-local
fi

awslocal s3 mb s3://pa-uploads-local
awslocal s3 mb s3://sirius-bucket-local
awslocal s3 mb s3://opg-performance-data

awslocal s3 cp /tmp/paProDeputyReport.csv s3://sirius-bucket-local/paProDeputyReport.csv
awslocal s3 cp /tmp/layDeputyReport.csv s3://sirius-bucket-local/layDeputyReport.csv

awslocal s3api put-bucket-policy \
    --policy '{ "Statement": [ { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::csv-bucket/*", "Condition":  { "StringNotEquals": { "s3:x-amz-server-side-encryption": "AES256" } } }, { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::csv-bucket/*", "Condition":  { "Bool": { "aws:SecureTransport": false } } } ] }' \
    --bucket "pa-uploads-local"

awslocal s3api put-bucket-policy \
    --policy '{ "Statement": [ { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::sirius-bucket/*", "Condition":  { "StringNotEquals": { "s3:x-amz-server-side-encryption": "AES256" } } }, { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::sirius-bucket/*", "Condition":  { "Bool": { "aws:SecureTransport": false } } } ] }' \
    --bucket "sirius-bucket-local"

awslocal s3api put-bucket-policy \
    --policy '{ "Statement": [ { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::opg-performance-data/*", "Condition":  { "StringNotEquals": { "s3:x-amz-server-side-encryption": "AES256" } } }, { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::opg-performance-data/*", "Condition":  { "Bool": { "aws:SecureTransport": false } } } ] }' \
    --bucket "opg-performance-data"

awslocal ssm put-parameter --name "/local/flag/checklist-sync" --value "1" --type String --overwrite
awslocal ssm put-parameter --name "/local/flag/document-sync" --value "1" --type String --overwrite
awslocal ssm put-parameter --name "/local/flag/paper-reports" --value "0" --type String --overwrite
awslocal ssm put-parameter --name "/local/flag/multi-accounts" --value "0" --type String --overwrite

awslocal ssm put-parameter --name "/local/parameter/checklist-sync-row-limit" --value "100" --type String --overwrite
awslocal ssm put-parameter --name "/local/parameter/document-sync-interval-minutes" --value "4" --type String --overwrite
awslocal ssm put-parameter --name "/local/parameter/document-sync-row-limit" --value "100" --type String --overwrite

awslocal secretsmanager create-secret --name "local/opg-response-slack-token" --secret-string "IAMAFAKETOKEN"
awslocal secretsmanager create-secret --name "local/database-password" --secret-string "api"
# 64444001 is client for Lay-OPG102-4 Client 1.
awslocal secretsmanager create-secret --name "local/smoke-test-variables" --secret-string "{\"admin_user\":\"smoketestddadmin@smoketest.com\",\"admin_password\":\"DigidepsPass1234\",\"client\":\"64444001\",\"deputy_user\":\"lay-opg102-user-1@publicguardian.gov.uk\",\"deputy_password\":\"DigidepsPass1234\"}"

openssl genrsa -out private.pem 2048
openssl rsa -in private.pem -outform PEM -pubout -out public.pem
awslocal secretsmanager create-secret --name "local/private-jwt-key-base64" --secret-string "$(base64 private.pem)"
awslocal secretsmanager create-secret --name "local/public-jwt-key-base64" --secret-string "$(base64 public.pem)"

kid=$(echo -n $(base64 public.pem) | openssl dgst -sha256)
b64headers=$(echo -n "{\"typ\": \"JWT\", \"alg\": \"RS256\", \"jku\": \"http://frontend-webserver/v2/.well-known/jwks.json\", \"kid\": \"${kid}\"}"  | openssl base64 -e -A | tr '+/' '-_' | tr -d '=';)
b64payload=$(echo -n '{"aud": "urn:opg:registration_service","iat": 1659782970.135131,"exp": 1975402170.135139,"nbf": 1659782960.135146,"iss": "urn:opg:digideps"}' | openssl base64 -e -A | tr '+/' '-_' | tr -d '=';)
b64headandpay="${b64headers}.${b64payload}"
b64digest=$(echo -n ${b64headandpay} | openssl dgst -sha256 -sign private.pem -binary | openssl base64 -e -A | tr '+/' '-_' | tr -d '=';)
b64jwt=${b64headandpay}.${b64digest}

awslocal secretsmanager create-secret --name "local/synchronisation-jwt-token" --secret-string ${b64jwt}
rm private.pem public.pem
