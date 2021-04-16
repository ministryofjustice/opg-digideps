#!/bin/sh
set -e

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\\\\ServiceContainer\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"

../bin/behat --config=../behat/tests/behat.yml  --stop-on-failure $@
