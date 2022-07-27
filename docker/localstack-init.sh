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

openssl genrsa -out private.pem 2048
openssl rsa -in private.pem -outform PEM -pubout -out public.pem

awslocal secretsmanager create-secret --name "default/private-jwt-key-base64" --secret-string "$(base64 private.pem)"
awslocal secretsmanager create-secret --name "default/public-jwt-key-base64" --secret-string "$(base64 public.pem)"
awslocal secretsmanager create-secret --name "default/synchronise-jwt" --secret-string "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImprdSI6Imh0dHBzOi8vZGlnaWRlcHMubG9jYWwvdjIvLndlbGwta25vd24vandrcy5qc29uIiwia2lkIjoiYTY0NTM5YjIzOTk2ZGMxYWQxMzBiYTljNmY0OWUyNjk4NWE1MjMyMDk3NjAxNmM2ZDQxMzcwODE4ZDgxYWIwOCJ9.eyJhdWQiOiJ1cm46b3BnOnJlZ2lzdHJhdGlvbl9zZXJ2aWNlIiwiaWF0IjoxNjU4OTA4OTY5LjI2NTUwMSwiZXhwIjoxNjU4OTEyNTY5LjI2NTUxLCJuYmYiOjE2NTg5MDg5NTkuMjY1NTE1LCJpc3MiOiJ1cm46b3BnOmRpZ2lkZXBzIn0.rOE_xpwl966v26GVJqJsU7MaqKC4EbE5CBtH176z2hfuYLaOxxuGt39t5YbQvZdymd8mHWcnsWumv4fudZZqzGzg1BSPllywFaLJtvvAJSti-NxGGHU8eeszc6LsW6ryfcLxtL29D6mMCmbN90v98muxKM6KMKWEWIS1uKzMGGwp7ZWJWYL7l9VE039skZhRKNs0T96ySktF4OfNj46Z7x4QSsC5MJEOl8hLPWUB6Bwq9ie09DVOos8apdTU2ac8yZ54PmTlkH5Ir9-A57NKg4IEC1zSfSe2PgsSI2BlJj_xx8aHCQtOfXcqvjnEZUZkHV_7e1xOjLMfJsQeYTjpiw"
rm private.pem public.pem
