#!/bin/sh
set -e

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev

confd -onetime -backend env

su-exec www-data php app/console doctrine:database:drop --force
su-exec www-data php app/console doctrine:database:create
su-exec www-data php app/console doctrine:migrations:migrate --no-interaction

./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure $@
