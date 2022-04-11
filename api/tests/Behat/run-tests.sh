#!/bin/sh
set -e

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev

confd -onetime -backend env
./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure --profile v2-tests-goutte $@
