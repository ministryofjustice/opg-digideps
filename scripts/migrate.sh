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
# takes time. only enable when needed
#/sbin/setuser app php app/console digideps:fix-data --env=prod

# TO RUN for release 14/15 June 2017
#/sbin/setuser app php app/console digideps:fix-reporting-periods --env=prod
#/sbin/setuser app php app/console digideps:fix-report-submitted-by --env=prod
# TO RUN for release 14/15 June 2017 - END