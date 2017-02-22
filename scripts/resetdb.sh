#!/usr/bin/env bash
set -e
# We need below to create the params file on container start
run-parts /etc/my_init.d

source initialize_schema.sh

/sbin/setuser app php app/console doctrine:migrations:status-check
/sbin/setuser app php app/console doctrine:migrations:migrate --no-interaction -vvv
/sbin/setuser app php app/console digideps:fixtures
