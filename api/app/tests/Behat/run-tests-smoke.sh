#!/bin/sh
set -e

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\/\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev

if [ -f ./api.env ]; then
  echo "== Sourcing env from api.env (only missing vars) =="

  while IFS='=' read -r key value || [ -n "$key" ]; do
    # Skip empty lines or comments
    case "$key" in
      ''|\#*) continue ;;
    esac

    # Strip possible surrounding quotes
    value=$(echo "$value" | sed -e 's/^"\(.*\)"$/\1/' -e "s/^'\(.*\)'$/\1/")

    # Check if variable is already set
    eval "is_set=\${$key+x}"

    if [ -z "$is_set" ]; then
      export "$key=$value"
    fi
  done < ./api.env
fi

if [ -f ./tests/Behat/test.env ]; then
  echo "== Sourcing env from test.env (only missing vars) =="

  while IFS='=' read -r key value || [ -n "$key" ]; do
    # Skip empty lines or comments
    case "$key" in
      ''|\#*) continue ;;
    esac

    # Strip possible surrounding quotes
    value=$(echo "$value" | sed -e 's/^"\(.*\)"$/\1/' -e "s/^'\(.*\)'$/\1/")

    # Check if variable is already set
    eval "is_set=\${$key+x}"

    if [ -z "$is_set" ]; then
      export "$key=$value"
    fi
  done < ./tests/Behat/test.env
fi

./vendor/bin/behat --config=./tests/Behat/behat.yml  --tags '@smoke'  --stop-on-failure $@
