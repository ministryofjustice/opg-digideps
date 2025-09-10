#!/bin/sh

# Export variables (.env files not auto loaded)
cat ./api.env ./tests/Behat/test.env > /tmp/combined.env
while IFS= read -r line; do
    eval "$line"
done < <(./tests/Behat/source-env-files.sh /tmp/combined.env)
rm /tmp/combined.env

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST/\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev

start=$(date +%s)

MAX_RETRIES=2
ATTEMPT=0
FAILED_SCENARIOS_FILE="/tmp/behat_failure_output.txt"

echo "==== Starting test run ===="
set -o pipefail

while [ $ATTEMPT -le $MAX_RETRIES ]; do
    ATTEMPT=$((ATTEMPT+1))
    echo "==== Behat attempt $ATTEMPT/$((MAX_RETRIES+1)) ===="

    if [ $ATTEMPT -eq 1 ]; then
        # First run: all scenarios
        ./vendor/bin/behat --config=./tests/Behat/behat.yml --profile v2-tests-browserkit --list-scenarios "$@" \
            | ./vendor/liuggio/fastest/fastest -vv "./vendor/bin/behat --profile v2-tests-browserkit --tags @v2 --config=./tests/Behat/behat.yml {}" \
            | tee "$FAILED_SCENARIOS_FILE"
    else
        # Subsequent runs: only rerun failed scenarios
        FAILED_SCENARIOS=$(grep '^\[[0-9]\] /var/www/tests/Behat' "$FAILED_SCENARIOS_FILE" \
            | awk -F' ' '{print $2}' \
            | awk -F'@' '{print $1}')

        if [ -z "$FAILED_SCENARIOS" ]; then
            echo "No failed scenarios to rerun."
            break
        fi

        ./vendor/liuggio/fastest/fastest "./vendor/bin/behat --profile v2-tests-browserkit --tags @v2 --config=./tests/Behat/behat.yml {}" <<< "$FAILED_SCENARIOS" \
            | tee "$FAILED_SCENARIOS_FILE"
    fi

    if [ $? -eq 0 ]; then
        echo "==== Tests passed ===="
        break
    else
        echo "==== Attempt $ATTEMPT failed ===="
        if [ $ATTEMPT -gt $MAX_RETRIES ]; then
            echo "==== All retries exhausted. Exiting with failure ===="
            exit 1
        fi
        echo "==== Retrying failed scenarios ===="
    fi
done

end=$(date +%s)
runtime=$(( end - start))

echo "Time taken: ${runtime} secs"

if [ "$WORKSPACE" = "integration" ]; then
    max_time=600
else
    max_time=420
fi

if [ $runtime -gt $max_time ]; then
    echo "Stage taking too long. Failing the build!"
    echo "Please split out your tests to a new container"
    exit 1
fi
