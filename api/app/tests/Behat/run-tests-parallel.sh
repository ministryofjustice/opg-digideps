#!/bin/sh

# Export variables (.env files not auto loaded)
cat ./api.env ./tests/Behat/test.env > /tmp/combined.env
while IFS= read -r line; do
    eval "$line"
done < <(./tests/Behat/source-env-files.sh /tmp/combined.env)
rm /tmp/combined.env
export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\/\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev

start=$(date +%s)

echo "==== Starting test run ===="
set -o pipefail
./vendor/bin/behat --config=./tests/Behat/behat.yml --profile v2-tests-browserkit --list-scenarios $@ | ./vendor/liuggio/fastest/fastest -vv "./vendor/bin/behat --profile v2-tests-browserkit --tags @v2 --config=./tests/Behat/behat.yml {}" | tee /tmp/behat_failure_output.txt
if [ $? -ne 0 ]; then
    echo "==== Rerunning failed tests once ===="
    grep '^\[[0-9]\] /var/www/tests/Behat' /tmp/behat_failure_output.txt | awk -F' ' '{print $2}' | awk -F'@' '{print $1}' | ./vendor/liuggio/fastest/fastest "./vendor/bin/behat --profile v2-tests-browserkit --tags @v2 --config=./tests/Behat/behat.yml {}"
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
