#!/usr/bin/env bash
set -e

# We need below to create the params file on container start
confd -onetime -backend env

php app/console doctrine:fixtures:load --no-interaction
php app/console doctrine:fixtures:load --no-interaction --env=test

./scripts/setup_custom_sql_query.sh
