set -e

groupExists=$( (awslocal logs describe-log-groups | jq '.logGroups[] | select(.logGroupName == "audit-local")') )

#Create log group if it does not exist (stops ResourceAlreadyExists errors)
if [ -z "$groupExists" ]
then
    awslocal logs create-log-group --log-group-name audit-local
fi

awslocal s3 mb s3://pa-uploads-local
awslocal s3 mb s3://sirius-bucket-local

awslocal s3 cp /tmp/paProDeputyReport.csv s3://sirius-bucket-local/paProDeputyReport.csv
awslocal s3 cp /tmp/layDeputyReport.csv s3://sirius-bucket-local/layDeputyReport.csv

awslocal s3api put-bucket-policy \
    --policy '{ "Statement": [ { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::csv-bucket/*", "Condition":  { "StringNotEquals": { "s3:x-amz-server-side-encryption": "AES256" } } }, { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::csv-bucket/*", "Condition":  { "Bool": { "aws:SecureTransport": false } } } ] }' \
    --bucket "pa-uploads-local"

awslocal s3api put-bucket-policy \
    --policy '{ "Statement": [ { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::sirius-bucket/*", "Condition":  { "StringNotEquals": { "s3:x-amz-server-side-encryption": "AES256" } } }, { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::sirius-bucket/*", "Condition":  { "Bool": { "aws:SecureTransport": false } } } ] }' \
    --bucket "sirius-bucket-local"

awslocal ssm put-parameter --name "/default/flag/checklist-sync" --value "1" --type String --overwrite
awslocal ssm put-parameter --name "/default/flag/document-sync" --value "1" --type String --overwrite
awslocal ssm put-parameter --name "/default/flag/paper-reports" --value "0" --type String --overwrite

awslocal ssm put-parameter --name "/default/parameter/checklist-sync-row-limit" --value "100" --type String --overwrite
awslocal ssm put-parameter --name "/default/parameter/document-sync-interval-minutes" --value "4" --type String --overwrite
awslocal ssm put-parameter --name "/default/parameter/document-sync-row-limit" --value "100" --type String --overwrite

awslocal secretsmanager create-secret --name "default/opg-response-slack-token" --secret-string "IAMAFAKETOKEN"
awslocal secretsmanager create-secret --name "default/database-password" --secret-string "api"

openssl genrsa -out private.pem 2048
openssl rsa -in private.pem -outform PEM -pubout -out public.pem

awslocal secretsmanager create-secret --name "default/private-jwt-key-base64" --secret-string "$(base64 private.pem)"
awslocal secretsmanager create-secret --name "default/public-jwt-key-base64" --secret-string "$(base64 public.pem)"

kid=$(echo -n $(base64 public.pem) | openssl dgst -sha256)
b64headers=$(echo -n "{\"typ\": \"JWT\", \"alg\": \"RS256\", \"jku\": \"https://digideps.local/v2/.well-known/jwks.json\", \"kid\": \"${kid}\"}"  | openssl base64 -e -A | tr '+/' '-_' | tr -d '=';)
b64payload=$(echo -n '{"aud": "urn:opg:registration_service","iat": 1659782970.135131,"exp": 1975402170.135139,"nbf": 1659782960.135146,"iss": "urn:opg:digideps"}' | openssl base64 -e -A | tr '+/' '-_' | tr -d '=';)
b64headandpay="${b64headers}.${b64payload}"
b64digest=$(echo -n ${b64headandpay} | openssl dgst -sha256 -sign private.pem -binary | openssl base64 -e -A | tr '+/' '-_' | tr -d '=';)
b64jwt=${b64headandpay}.${b64digest}

awslocal secretsmanager create-secret --name "default/synchronisation-jwt-token" --secret-string ${b64jwt}
rm private.pem public.pem
