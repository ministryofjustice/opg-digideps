#!/usr/bin/env bash
set -e
# We need below to create the params file on container start
confd -onetime -backend env

. /var/www/scripts/initialize_schema.sh

su-exec www-data php app/console doctrine:migrations:status-check
su-exec www-data php app/console doctrine:migrations:migrate --no-interaction -vvv
su-exec www-data php app/console doctrine:fixtures:load --no-interaction
