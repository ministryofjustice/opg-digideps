#!/usr/bin/env bash
set -e
#account is shared-prod as all env use prod.
PACT_BROKER_ACCOUNT="997462338508"
PACT_BROKER_HTTP_AUTH_USER="admin"
PACT_BROKER_BASE_URL="pact-broker.api.opg.service.justice.gov.uk"
PACT_API_VERSION="v1"

if [ $(aws --version 2>&1 | grep -c "aws-cli") -ne 0 ]
then
  export SECRET_STRING=$(aws sts assume-role \
  --role-arn "arn:aws:iam::${PACT_BROKER_ACCOUNT}:role/get-pact-secret-production" \
  --role-session-name AWSCLI-Session | \
  jq -r '.Credentials.SessionToken + " " + .Credentials.SecretAccessKey + " " + .Credentials.AccessKeyId' 2>/dev/null)

  #local export so they only exist in this stage
  export AWS_ACCESS_KEY_ID=$(echo "${SECRET_STRING}" | awk -F' ' '{print $3}' 2>/dev/null)
  export AWS_SECRET_ACCESS_KEY=$(echo "${SECRET_STRING}" | awk -F' ' '{print $2}' 2>/dev/null)
  export AWS_SESSION_TOKEN=$(echo "${SECRET_STRING}" | awk -F' ' '{print $1}' 2>/dev/null)

  export PACT_BROKER_HTTP_AUTH_PASSWORD=$(aws secretsmanager get-secret-value \
  --secret-id pactbroker_admin \
  --region eu-west-1 | jq -r '.SecretString' 2>/dev/null)

  if [[ -z "${PACT_BROKER_HTTP_AUTH_PASSWORD}" ]]
  then
    echo "Error setting env var PACT_BROKER_HTTP_AUTH_PASSWORD"
  else
    echo "Env var PACT_BROKER_HTTP_AUTH_PASSWORD has been set"
  fi
fi

PACT_CONSUMER_VERSION=${CIRCLE_SHA1:0:7}

echo "export PACT_BROKER_BASE_URL=${PACT_BROKER_BASE_URL}"
echo "export PACT_CONSUMER_VERSION=${PACT_CONSUMER_VERSION}"
echo "export PACT_BROKER_HTTP_AUTH_USER=${PACT_BROKER_HTTP_AUTH_USER}"
echo "export PACT_BROKER_HTTP_AUTH_PASSWORD=${PACT_BROKER_HTTP_AUTH_PASSWORD}"
echo "export PACT_API_VERSION=${PACT_API_VERSION}"
echo "export PACT_BROKER_ACCOUNT=${PACT_BROKER_ACCOUNT}"
