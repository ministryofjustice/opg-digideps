#!/bin/sh
set -e

. $BASH_ENV

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\\\\ServiceContainer\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"

vendor/bin/behat --config=tests/behat.yml  --stop-on-failure $@
