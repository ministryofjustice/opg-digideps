#!/bin/sh
set -e

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev


./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure --profile v2-tests-goutte --tags @gifts &
P1=$!
./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure --profile v2-tests-goutte --tags @money-out &
P2=$!
./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure --profile v2-tests-goutte --tags @additional-information &
P3=$!
./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure --profile v2-tests-goutte --tags @actions &
P4=$!
./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure --profile v2-tests-goutte --tags @money-out-short &
P5=$!
wait $P1 $P2 $P3 $P4 $P5
