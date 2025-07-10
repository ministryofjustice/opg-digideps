#!/bin/sh
set -e

# Export variables (.env files not auto loaded)
cat ./api.env ./tests/Behat/test.env > /tmp/combined.env
while IFS= read -r line; do
    eval "$line"
done < <(./tests/Behat/source-env-files.sh /tmp/combined.env)
rm /tmp/combined.env
export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\/\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev

./vendor/bin/behat --config=./tests/Behat/behat.yml --profile v2-tests-browserkit --tags '@smoke'  --stop-on-failure $@
