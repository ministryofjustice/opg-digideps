#!/bin/sh
set -e

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\/\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev
if [ -f ./api.env ]; then
  echo "== Sourcing env from api.env =="
  set -a
  source ./api.env
  set +a
fi

if [ -f ./tests/Behat/test.env ]; then
  echo "== Sourcing env from test.env =="
  set -a
  source ./tests/Behat/test.env
  set +a
fi

./vendor/bin/behat --config=./tests/Behat/behat.yml  --tags '@smoke'  --stop-on-failure $@
