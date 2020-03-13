#!/usr/bin/env bash
set -e
#account is shared-prod as all env use prod.
ACCOUNT="997462338508"
PACT_BROKER_USER="admin"
PACT_BROKER_URL="pact-broker.api.opg.service.justice.gov.uk"

export SECRETSTRING=$(aws sts assume-role \
--role-arn "arn:aws:iam::${ACCOUNT}:role/get-pact-secret-development" \
--role-session-name AWSCLI-Session | \
jq -r '.Credentials.SessionToken + " " + .Credentials.SecretAccessKey + " " + .Credentials.AccessKeyId')

#local export so they only exist in this stage
export AWS_ACCESS_KEY_ID=$(echo "${SECRETSTRING}" | awk -F' ' '{print $3}')
export AWS_SECRET_ACCESS_KEY=$(echo "${SECRETSTRING}" | awk -F' ' '{print $2}')
export AWS_SESSION_TOKEN=$(echo "${SECRETSTRING}" | awk -F' ' '{print $1}')

export PACT_BROKER_PASS=$(aws secretsmanager get-secret-value \
--secret-id pactbroker_admin \
--region eu-west-1 | jq -r '.SecretString')

WORKSPACE=${WORKSPACE:-$CIRCLE_BRANCH}
WORKSPACE=${WORKSPACE//[^[:alnum:]]/}
WORKSPACE=${WORKSPACE,,}
WORKSPACE=${WORKSPACE:0:14}
CONSUMER_VERSION=${CIRCLE_SHA1:0:7}
VERSION=${VERSION:-$(cat ~/project/VERSION 2>/dev/null)}

echo "export PACT_TAG=${CIRCLE_BRANCH}"
echo "export PACT_BROKER_BASE_URL=${PACT_BROKER_URL}"
echo "export PACT_CONSUMER_VERSION=${CONSUMER_VERSION}"
echo "export PACT_BROKER_HTTP_AUTH_USER=${PACT_BROKER_USER}"
echo "export PACT_BROKER_HTTP_AUTH_PASS=${PACT_BROKER_PASS}"
echo "export TF_WORKSPACE=${WORKSPACE}"
echo "export TF_VAR_OPG_DOCKER_TAG=${VERSION}"
echo "export VERSION=${VERSION}"
