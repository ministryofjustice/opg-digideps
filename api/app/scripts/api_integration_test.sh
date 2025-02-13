#!/bin/bash
# exit on error
set -e

# Generate config files so test bootstrap can address the DB
confd -onetime -backend env

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

# Check the argument provided and run the corresponding test suites
case "$1" in
  selection-all)
    printf '\n Running Stats Suite \n\n'
    php vendor/bin/phpunit -c tests/Unit tests/Integration/Stats/ --coverage-php tests/coverage/Stats.cov
    printf '\n Running Command Suite \n\n'
    php vendor/bin/phpunit -c tests/Unit tests/Integration/Command/ --coverage-php tests/coverage/Command.cov
    printf '\n Running Controller Suite \n\n'
    php vendor/bin/phpunit -c tests/Unit tests/Integration/Controller/ --coverage-php tests/coverage/Controller.cov
    printf '\n Running Controller-Ndr Suite \n\n'
    php vendor/bin/phpunit -c tests/Unit tests/Integration/Controller-Ndr/ --coverage-php tests/coverage/Controller-Ndr.cov
    printf '\n Running ControllerReport Suite \n\n'
    php vendor/bin/phpunit -c tests/Unit tests/Integration/ControllerReport/ --coverage-php tests/coverage/ControllerReport.cov
    printf '\n Running DBAL Suite \n\n'
    php vendor/bin/phpunit --debug -c tests/Unit tests/Integration/DBAL/ --coverage-php tests/coverage/DBAL.cov
    printf '\n Running Entity Suite \n\n'
    php vendor/bin/phpunit -c tests/Unit tests/Integration/Entity/ --coverage-php tests/coverage/Entity.cov
    printf '\n Running Security Suite \n\n'
    php vendor/bin/phpunit -c tests/Unit tests/Integration/Security/ --coverage-php tests/coverage/Security.cov
    printf '\n Running v2 Suite \n\n'
    php vendor/bin/phpunit -c tests/Unit tests/Integration/v2/ --coverage-php tests/coverage/v2.cov

    # generate HTML coverage report
    php -d memory_limit=256M vendor/phpunit/phpcov/phpcov merge --html "./build/coverage-api" "./tests/coverage"
    ;;
  *)
    echo "Invalid argument. Please provide one of the following arguments: selection-all"
    exit 1
    ;;
esac

php vendor/phpunit/phpcov/phpcov merge --clover "./tests/coverage/api-integration-tests.xml" "./tests/coverage"
