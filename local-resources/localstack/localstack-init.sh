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


awslocal s3 cp /tmp/lay-1-row-invalid-report-type-1-valid-row.csv s3://sirius-bucket-local/lay-1-row-invalid-report-type-1-valid-row.csv
awslocal s3 cp /tmp/lay-1-row-missing-all-required-1-valid-row.csv s3://sirius-bucket-local/lay-1-row-missing-all-required-1-valid-row.csv
awslocal s3 cp /tmp/lay-1-row-missing-all-required-columns.csv s3://sirius-bucket-local/lay-1-row-missing-all-required-columns.csv
awslocal s3 cp /tmp/lay-1-row-special-chars.csv s3://sirius-bucket-local/lay-1-row-special-chars.csv
awslocal s3 cp /tmp/lay-1-row-updated-report-type.csv s3://sirius-bucket-local/lay-1-row-updated-report-type.csv
awslocal s3 cp /tmp/lay-2-rows-co-deputy.csv s3://sirius-bucket-local/lay-2-rows-co-deputy.csv
awslocal s3 cp /tmp/lay-3-valid-rows.csv s3://sirius-bucket-local/lay-3-valid-rows.csv
awslocal s3 cp /tmp/org-1-row-1-named-deputy-with-org-name-no-first-last-name.csv s3://sirius-bucket-local/org-1-row-1-named-deputy-with-org-name-no-first-last-name.csv
awslocal s3 cp /tmp/org-1-row-existing-named-deputy-and-client-new-org-and-street-address.csv s3://sirius-bucket-local/org-1-row-existing-named-deputy-and-client-new-org-and-street-address.csv
awslocal s3 cp /tmp/org-1-row-missing-all-required-columns.csv s3://sirius-bucket-local/org-1-row-missing-all-required-columns.csv
awslocal s3 cp /tmp/org-1-row-missing-last-report-date-1-valid-row.csv s3://sirius-bucket-local/org-1-row-missing-last-report-date-1-valid-row.csv
awslocal s3 cp /tmp/org-1-row-new-named-deputy-and-org-existing-client.csv s3://sirius-bucket-local/org-1-row-new-named-deputy-and-org-existing-client.csv
awslocal s3 cp /tmp/org-1-row-with-ndr-column.csv s3://sirius-bucket-local/org-1-row-with-ndr-column.csv
awslocal s3 cp /tmp/org-1-updated-row-existing-case-number-new-made-date.csv s3://sirius-bucket-local/org-1-updated-row-existing-case-number-new-made-date.csv
awslocal s3 cp /tmp/org-1-updated-row-named-deputy-address.csv s3://sirius-bucket-local/org-1-updated-row-named-deputy-address.csv
awslocal s3 cp /tmp/org-1-updated-row-new-named-deputy.csv s3://sirius-bucket-local/org-1-updated-row-new-named-deputy.csv
awslocal s3 cp /tmp/org-1-updated-row-report-type.csv s3://sirius-bucket-local/org-1-updated-row-report-type.csv
awslocal s3 cp /tmp/org-2-rows-1-named-deputy-with-different-addresses.csv s3://sirius-bucket-local/org-2-rows-1-named-deputy-with-different-addresses.csv
awslocal s3 cp /tmp/org-2-rows-1-person-deputy-1-org-deputy-updated-emails.csv s3://sirius-bucket-local/org-2-rows-1-person-deputy-1-org-deputy-updated-emails.csv
awslocal s3 cp /tmp/org-2-rows-1-person-deputy-1-org-deputy-updated-names.csv s3://sirius-bucket-local/org-2-rows-1-person-deputy-1-org-deputy-updated-names.csv
awslocal s3 cp /tmp/org-2-rows-1-person-deputy-1-org-deputy.csv s3://sirius-bucket-local/org-2-rows-1-person-deputy-1-org-deputy.csv
awslocal s3 cp /tmp/org-2-rows-1-row-updated-report-type-dual-case.csv s3://sirius-bucket-local/org-2-rows-1-row-updated-report-type-dual-case.csv
awslocal s3 cp /tmp/org-3-valid-rows.csv s3://sirius-bucket-local/org-3-valid-rows.csv
awslocal s3 cp /tmp/org-2-rows-1-person-deputy-1-org-deputy-2ndRun.csv s3://sirius-bucket-local/org-2-rows-1-person-deputy-1-org-deputy-2ndRun.csv

awslocal s3api put-bucket-policy \
    --policy '{ "Statement": [ { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::csv-bucket/*", "Condition":  { "StringNotEquals": { "s3:x-amz-server-side-encryption": "AES256" } } }, { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::csv-bucket/*", "Condition":  { "Bool": { "aws:SecureTransport": false } } } ] }' \
    --bucket "pa-uploads-local"

awslocal s3api put-bucket-policy \
    --policy '{ "Statement": [ { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::sirius-bucket/*", "Condition":  { "StringNotEquals": { "s3:x-amz-server-side-encryption": "AES256" } } }, { "Sid": "DenyUnEncryptedObjectUploads", "Effect": "Deny", "Principal": { "AWS": "*" }, "Action": "s3:PutObject", "Resource": "arn:aws:s3:eu-west-1::sirius-bucket/*", "Condition":  { "Bool": { "aws:SecureTransport": false } } } ] }' \
    --bucket "sirius-bucket-local"

awslocal ssm put-parameter --name "/local/flag/checklist-sync" --value "1" --type String --overwrite
awslocal ssm put-parameter --name "/local/flag/document-sync" --value "1" --type String --overwrite
awslocal ssm put-parameter --name "/local/flag/paper-reports" --value "0" --type String --overwrite

awslocal ssm put-parameter --name "/local/parameter/checklist-sync-row-limit" --value "100" --type String --overwrite
awslocal ssm put-parameter --name "/local/parameter/document-sync-interval-minutes" --value "4" --type String --overwrite
awslocal ssm put-parameter --name "/local/parameter/document-sync-row-limit" --value "100" --type String --overwrite

awslocal secretsmanager create-secret --name "local/opg-response-slack-token" --secret-string "IAMAFAKETOKEN"
awslocal secretsmanager create-secret --name "local/database-password" --secret-string "api"

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
