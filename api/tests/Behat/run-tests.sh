#!/bin/sh
set -e

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev

confd -onetime -backend env
#su-exec www-data php app/console doctrine:fixtures:load --no-interaction
./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure $@
