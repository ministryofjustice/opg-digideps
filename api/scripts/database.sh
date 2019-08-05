#!/bin/bash
echo "This script is deprecated. Use migrate.sh instead"
set -e
#let's configure environment
confd -onetime -backend env

cd /var/www
su-exec www-data php app/console doctrine:migrations:status-check
su-exec www-data php app/console doctrine:migrations:migrate --no-interaction -vvv
su-exec www-data php app/console doctrine:fixtures:load --no-interaction
