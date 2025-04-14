#!/bin/sh

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\/\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev

start=$(date +%s)

echo "==== Starting test run ===="
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

end=$(date +%s)

runtime=$(( end - start))

echo "Time taken: ${runtime} secs"

if [ "$WORKSPACE" = "integration" ]; then
    max_time=600
else
    max_time=420
fi

if [ $runtime -gt $max_time ]
then
    echo "Stage taking too long. Failing the build!"
    echo "Please split out your tests to a new container"
    exit 1
fi
