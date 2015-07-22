#!/bin/bash
#let's configure environment
run-parts /etc/my_init.d
chown app:app /tmp/behat

cd /app
/sbin/setuser app mkdir -p /tmp/behat
apt-get install postgresql -y > /dev/null 2>&1
export PGHOST=postgres
export PGPASSWORD=api
export PGDATABASE=api
export PGUSER=api
rm -rf app/cache/*
/sbin/setuser app bin/behat --suite=deputy --stop-on-failure

