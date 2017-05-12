#!/bin/bash
set -e
#let's configure environment
run-parts /etc/my_init.d

cd /app
/sbin/setuser app php app/console doctrine:migrations:status-check
/sbin/setuser app php app/console doctrine:migrations:migrate --no-interaction -vvv
# add default users
/sbin/setuser app php app/console digideps:fixtures
# add missing data potentially notmissing due to failing migrations or previous bugs on data listeners
# takes several minutes. only enable when needed
/sbin/setuser app php app/console digideps:fix-data --env=prod
