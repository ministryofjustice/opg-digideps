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

echo "==== Starting test run ===="

while [ $ATTEMPT -le $MAX_RETRIES ]; do
    ATTEMPT=$((ATTEMPT+1))
    echo "==== Behat attempt $ATTEMPT/$((MAX_RETRIES+1)) ===="

    ./vendor/bin/behat --config=./tests/Behat/behat.yml --rerun --profile v2-tests-browserkit "$@"
    RESULT=$?

    if [ $RESULT -eq 0 ]; then
        echo "==== Tests passed ===="
        break
    else
        echo "==== Attempt $ATTEMPT failed ===="
        if [ $ATTEMPT -gt $MAX_RETRIES ]; then
            echo "==== All retries exhausted. Exiting with failure ===="
            exit 1
        fi
        echo "==== Retrying failed scenarios ===="
        # On retry, --rerun will automatically pick up failed scenarios from last run
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
