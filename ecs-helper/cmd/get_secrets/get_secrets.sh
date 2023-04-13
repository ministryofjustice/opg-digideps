if [ $(aws --version 2>&1 | grep -c "aws-cli") -ne 0 ]
then
  export SECRET_STRING=$(aws sts assume-role \
  --role-arn "arn:aws:iam::248804316466:role/digideps-ci" \
  --role-session-name AWSCLI-Session | \
  jq -r '.Credentials.SessionToken + " " + .Credentials.SecretAccessKey + " " + .Credentials.AccessKeyId' 2>/dev/null)
  #local export so they only exist in this stage
  export AWS_ACCESS_KEY_ID=$(echo "${SECRET_STRING}" | awk -F' ' '{print $3}' 2>/dev/null)
  export AWS_SECRET_ACCESS_KEY=$(echo "${SECRET_STRING}" | awk -F' ' '{print $2}' 2>/dev/null)
  export AWS_SESSION_TOKEN=$(echo "${SECRET_STRING}" | awk -F' ' '{print $1}' 2>/dev/null)
  export BROWSERSTACK_USERNAME=$(aws secretsmanager get-secret-value \
  --secret-id development/browserstack-username \
  --region eu-west-1 | jq -r '.SecretString' 2>/dev/null)
  export BROWSERSTACK_KEY=$(aws secretsmanager get-secret-value \
  --secret-id development/browserstack-access-key \
  --region eu-west-1 | jq -r '.SecretString' 2>/dev/null)
  echo "BROWSERSTACK_USERNAME=$BROWSERSTACK_USERNAME" >> ~/project/behat/.env
  echo "BROWSERSTACK_KEY=$BROWSERSTACK_KEY" >> ~/project/behat/.env
  if [[ -z "${BROWSERSTACK_USERNAME}" ]]
  then
    echo "Error setting env var BROWSERSTACK_USERNAME"
  else
    echo "Env var BROWSERSTACK_USERNAME has been set"
  fi
  if [[ -z "${BROWSERSTACK_KEY}" ]]
  then
    echo "Error setting env var BROWSERSTACK_KEY"
  else
    echo "Env var BROWSERSTACK_KEY has been set"
  fi
fi
