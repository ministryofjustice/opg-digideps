#!/bin/bash
set -e
#let's configure environment
confd -onetime -backend env

cd /var/www

su-exec www-data php app/console doctrine:migrations:status-check
su-exec www-data php app/console doctrine:migrations:migrate --no-interaction -vvv

# add default users
su-exec www-data php app/console doctrine:fixtures:load --no-interaction

# add missing data potentially notmissing due to failing migrations or previous bugs on data listeners. Slow, only enable if/when needed
# /sbin/setuser app php app/console digideps:fix-data --env=prod
