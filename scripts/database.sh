#!/bin/bash
echo "This script is deprecated. Use migrate.sh instead"
set -e
#let's configure environment
run-parts /etc/my_init.d

cd /app
/sbin/setuser app php app/console doctrine:migrations:status-check
/sbin/setuser app php app/console doctrine:migrations:migrate --no-interaction -vvv
/sbin/setuser app php app/console doctrine:fixtures:load --no-interaction
