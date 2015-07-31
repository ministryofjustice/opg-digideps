#!/bin/bash
#let's configure environment
run-parts /etc/my_init.d

cd /app
/sbin/setuser app php app/console doctrine:query:sql "DROP DATABASE IF EXISTS digideps_unit_test;"
/sbin/setuser app php app/console doctrine:query:sql "CREATE DATABASE digideps_unit_test;"