#!/bin/sh

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\/\", \"browser_stack\": { \"username\": \"$BROWSERSTACK_USERNAME\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
export APP_ENV=dev

if [ -f ./api.env ]; then
  echo "== Sourcing env from api.env (only missing vars) =="

  while IFS='=' read -r key value || [ -n "$key" ]; do
    # Skip empty lines or comments
    case "$key" in
      ''|\#*) continue ;;
    esac

    # Skip keys that start with AWS_
    case "$key" in
      AWS_*) continue ;;
    esac

    # Strip possible surrounding quotes
    value=$(echo "$value" | sed -e 's/^"\(.*\)"$/\1/' -e "s/^'\(.*\)'$/\1/")

    # Check if variable is already set
    eval "is_set=\${$key+x}"

    if [ -z "$is_set" ]; then
      export "$key=$value"
    fi
  done < ./api.env
fi

if [ -f ./tests/Behat/test.env ]; then
  echo "== Sourcing env from test.env (only missing vars) =="

  while IFS='=' read -r key value || [ -n "$key" ]; do
    # Skip empty lines or comments
    case "$key" in
      ''|\#*) continue ;;
    esac

    # Skip keys that start with AWS_
    case "$key" in
      AWS_*) continue ;;
    esac

    # Strip possible surrounding quotes
    value=$(echo "$value" | sed -e 's/^"\(.*\)"$/\1/' -e "s/^'\(.*\)'$/\1/")

    # Check if variable is already set
    eval "is_set=\${$key+x}"

    if [ -z "$is_set" ]; then
      export "$key=$value"
    fi
  done < ./tests/Behat/test.env
fi

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
