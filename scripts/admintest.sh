#!/bin/bash
bash scripts/configureEnvironment.sh

# environment variables for psql command
export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=api}
export PGUSER=${API_DATABASE_USERNAME:=api}

/sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=admin --profile=${PROFILE:=headless} --stop-on-failure
