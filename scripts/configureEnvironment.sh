#!/bin/bash
set -e
#let's configure environment
run-parts /etc/my_init.d
chown app:app /tmp/behat
chown app:app /tmp

# create log dir locally failing sometimes)
mkdir -p /var/log/app
chown app:app /var/log/app

cd /app
/sbin/setuser app mkdir -p /tmp/behat
rm -rf app/cache/*
