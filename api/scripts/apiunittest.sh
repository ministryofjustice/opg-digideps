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
export PGDATABASE=${DATABASE_NAME:=digideps_unit_test}
export PGUSER=${DATABASE_USERNAME:=api}

# Run each folder of unit tests individually. If we were to run them all
# individually it would cause a memory leak.
printf '\n Running DBAL Suite \n\n'
php vendor/bin/phpunit -c tests/Unit tests/Unit/DBAL/
#printf '\n Running Command Suite \n\n'
#php vendor/bin/phpunit -c tests/Unit tests/Unit/Command/ --coverage-php tests/coverage/Command.cov
#printf '\n Running Controller Suite \n\n'
#php vendor/bin/phpunit -c tests/Unit tests/Unit/Controller/ --coverage-php tests/coverage/Controller.cov
#printf '\n Running ControllerReport Suite \n\n'
#php vendor/bin/phpunit -c tests/Unit tests/Unit/ControllerReport/ --coverage-php tests/coverage/ControllerReport.cov
#printf '\n Running Controller-Ndr Suite \n\n'
#php vendor/bin/phpunit -c tests/Unit tests/Unit/Controller-Ndr/ --coverage-php tests/coverage/Controller-Ndr.cov
#printf '\n Running Entity Suite \n\n'
#php vendor/bin/phpunit -c tests/Unit tests/Unit/Entity/ --coverage-php tests/coverage/Entity.cov
#printf '\n Running Factory Suite \n\n'
#php vendor/bin/phpunit -c tests/Unit tests/Unit/Factory/ --coverage-php tests/coverage/Factory.cov
#printf '\n Running Security Suite \n\n'
#php vendor/bin/phpunit -c tests/Unit tests/Unit/Security/ --coverage-php tests/coverage/Security.cov
#printf '\n Running Service Suite \n\n'
#php vendor/bin/phpunit -c tests/Unit tests/Unit/Service/ --coverage-php tests/coverage/Service.cov
#printf '\n Running Stats Suite \n\n'
#php vendor/bin/phpunit -c tests/Unit tests/Unit/Stats/ --coverage-php tests/coverage/Stats.cov
#printf '\n Running Transformer Suite \n\n'
#php vendor/bin/phpunit -c tests/Unit tests/Unit/Transformer/ --coverage-php tests/coverage/Transformer.cov
#printf '\n Running v2 Suite \n\n'
#php vendor/bin/phpunit -c tests/Unit tests/Unit/v2/ --coverage-php tests/coverage/v2.cov
#
#php vendor/phpunit/phpcov/phpcov merge --clover "./tests/coverage/api-unit-tests.xml" "./tests/coverage"
