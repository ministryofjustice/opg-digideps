#!/usr/bin/env bash
set -e

environment=${1:-development}

php app/console doctrine:fixtures:load --no-interaction

# Only run the test fixtures load if the environment is 'local'
if [ "$environment" == "local" ]; then
  php app/console doctrine:fixtures:load --no-interaction --env=test
fi

# Run the custom SQL query setup script
./scripts/setup_custom_sql_query.sh
./scripts/setup_readonly_sql_query.sh
