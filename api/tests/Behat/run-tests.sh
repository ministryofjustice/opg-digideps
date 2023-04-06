#!/bin/sh

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\/\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev

confd -onetime -backend env
# check if the last command exited with failure
./vendor/bin/behat --config=./tests/Behat/behat.yml --rerun --profile v2-tests-browserkit $@
if [ $? -ne 0 ]; then
    echo "==== Rerunning failed tests once ===="
    ./vendor/bin/behat --config=./tests/Behat/behat.yml --rerun --profile v2-tests-browserkit $@
    if [ $? -ne 0 ]; then
        echo "==== Reruns failed. Exiting with failure ===="
        exit 1
    else
        echo "==== Reruns successful. Exiting with success ===="
        exit 0
    fi
fi
