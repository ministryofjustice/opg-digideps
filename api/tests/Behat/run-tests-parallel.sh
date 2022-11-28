#!/bin/sh
set -e

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev

start=$(date +%s)

confd -onetime -backend env
./vendor/bin/behat --config=./tests/Behat/behat.yml --stop-on-failure --profile v2-tests-browserkit --list-scenarios $@ | ./vendor/liuggio/fastest/fastest -vvv "./vendor/bin/behat --profile v2-tests-browserkit --tags @v2 --config=./tests/Behat/behat.yml {}"

end=$(date +%s)

runtime=$(( end - start))

echo "Time taken: ${runtime} secs"

if [ $runtime -gt 600 ]
then
    echo "Stage taking too long. Failing the build!"
    echo "Please split out your tests to a new container"
    exit 1
fi
