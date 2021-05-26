#!/bin/sh
set -e

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev

start=`date +%s`

#./vendor/bin/behat --config=./tests/Behat/behat.yml --stop-on-failure --profile v2-tests-goutte --list-scenarios | grep v2 | ./vendor/liuggio/fastest/fastest "./vendor/bin/behat --profile v2-tests-goutte --config=./tests/Behat/behat.yml {}"




./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure --profile v2-tests-goutte --tags @v2
#P1=$!
#./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure --profile v2-tests-goutte --tags @money-out &
#P2=$!
#./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure --profile v2-tests-goutte --tags @additional-information &
#P3=$!
#./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure --profile v2-tests-goutte --tags @actions &
#P4=$!
#./vendor/bin/behat --config=./tests/Behat/behat.yml  --stop-on-failure --profile v2-tests-goutte --tags @money-out-short &
#P5=$!
#wait $P1 $P2 $P3 $P4 $P5

end=`date +%s`

runtime=$((end-start))

echo "Time take: ${runtime} secs"
