#!/bin/bash
# exit on error
set -e

ATTEMPTS=0

while curl -s http://localstack:4566/health | grep -v "\"initScripts\": \"initialized\""; do
  printf 'localstack initialisation scripts are still running\n'

  ATTEMPTS=$((ATTEMPTS+1))

  if [[ "$ATTEMPTS" -eq 20 ]]; then
      printf 'localstack failed to initialize\n'
      exit 1
  fi

  sleep 1s
done

printf '\n---localstack initialized---\n\n'

# Export unit test DB config so it can be used in tests
export PGHOST=${DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${DATABASE_PASSWORD:=api}
export PGDATABASE=digideps_unit_test
export PGUSER=${DATABASE_USERNAME:=api}
export SSL=${DATABASE_SSL:=allow}

INTEGRATION_SELECTION=$1
SUITE=$2
TEST_CASE=$3

# Check the argument provided and run the corresponding test suites
case "$INTEGRATION_SELECTION" in
  selection-1)
    # API Run 1
    printf '\n Running Controller Suite \n\n'
    php vendor/bin/phpunit -c tests/Integration tests/Integration/Controller/ --coverage-php tests/coverage/Controller.cov
    ;;
  selection-2)
    # API Run 2
    printf '\n Running ControllerReport Suite \n\n'
    php vendor/bin/phpunit -c tests/Integration tests/Integration/ControllerReport/ --coverage-php tests/coverage/ControllerReport.cov
    ;;
  selection-3)
    # API Run 3
    # IMPORTANT: these tests are order dependent, so don't rearrange them or try to run them as an aggregate
    printf '\n Running DBAL Suite \n\n'
    php vendor/bin/phpunit --debug -c tests/Integration tests/Integration/DBAL/ --coverage-php tests/coverage/DBAL.cov
    printf '\n Running Controller-Ndr Suite \n\n'
    php vendor/bin/phpunit -c tests/Integration tests/Integration/Controller-Ndr/ --coverage-php tests/coverage/Controller-Ndr.cov
    printf '\n Running Entity Suite \n\n'
    php vendor/bin/phpunit -c tests/Integration tests/Integration/Entity/ --coverage-php tests/coverage/Entity.cov
    printf '\n Running Command Suite \n\n'
    php vendor/bin/phpunit -c tests/Integration tests/Integration/Command/ --coverage-php tests/coverage/Command.cov
    printf '\n Running Security Suite \n\n'
    php vendor/bin/phpunit -c tests/Integration tests/Integration/Security/ --coverage-php tests/coverage/Security.cov
    printf '\n Running Stats Suite \n\n'
    php vendor/bin/phpunit -c tests/Integration tests/Integration/Stats/ --coverage-php tests/coverage/Stats.cov
    printf '\n Running v2 Suite \n\n'
    php vendor/bin/phpunit -c tests/Integration tests/Integration/v2/ --coverage-php tests/coverage/v2.cov
    ;;
  selection-all)
#    # selection-1
#    printf '\n Running Controller Suite \n\n'
#    php vendor/bin/phpunit -c tests/Integration tests/Integration/Controller/ --coverage-php tests/coverage/Controller.cov
#
#    # selection-2
#    printf '\n Running ControllerReport Suite \n\n'
#    php vendor/bin/phpunit -c tests/Integration tests/Integration/ControllerReport/ --coverage-php tests/coverage/ControllerReport.cov
#
#    # selection-3
#    printf '\n Running DBAL Suite \n\n'
#    php vendor/bin/phpunit --debug -c tests/Integration tests/Integration/DBAL/ --coverage-php tests/coverage/DBAL.cov
#    printf '\n Running Controller-Ndr Suite \n\n'
#    php vendor/bin/phpunit -c tests/Integration tests/Integration/Controller-Ndr/ --coverage-php tests/coverage/Controller-Ndr.cov
#    printf '\n Running Entity Suite \n\n'
#    php vendor/bin/phpunit -c tests/Integration tests/Integration/Entity/ --coverage-php tests/coverage/Entity.cov
#    printf '\n Running Command Suite \n\n'
#    php vendor/bin/phpunit -c tests/Integration tests/Integration/Command/ --coverage-php tests/coverage/Command.cov
#    printf '\n Running Security Suite \n\n'
#    php vendor/bin/phpunit -c tests/Integration tests/Integration/Security/ --coverage-php tests/coverage/Security.cov
#    printf '\n Running Stats Suite \n\n'
#    php vendor/bin/phpunit -c tests/Integration tests/Integration/Stats/ --coverage-php tests/coverage/Stats.cov
#    printf '\n Running v2 Suite \n\n'
    php vendor/bin/phpunit -c tests/Integration tests/Integration/ --coverage-php tests/coverage/v2.cov

    # generate HTML coverage report
    php -d memory_limit=256M vendor/phpunit/phpcov/phpcov merge --html "./build/coverage-api" "./tests/coverage"
    ;;
  selection-solo)
    #Run solo test suite
    if [[ -z "$TEST_CASE" ]]; then
      TEST_FILTER=""
    fi

    #Run solo test case in test suite
    if [[ -n "$TEST_CASE" ]]; then
      TEST_FILTER="--filter $TEST_CASE"
    fi

    printf "\nRunning Solo Test: %s %s \n\n" $SUITE $TEST_CASE
    php vendor/bin/phpunit -c tests/Integration $TEST_FILTER "tests/Integration/$SUITE"

    # generate HTML coverage report
    php -d memory_limit=256M vendor/phpunit/phpcov/phpcov merge --html "./build/coverage-api" "./tests/coverage"
    ;;
  *)
    echo "Invalid argument. Please provide one of the following arguments: selection-1, selection-2, selection-3, selection-all, selection-solo"
    exit 1
    ;;
esac

php vendor/phpunit/phpcov/phpcov merge --clover "./tests/coverage/api-integration-tests-$1.xml" "./tests/coverage"
