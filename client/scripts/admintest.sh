#!/bin/bash
#let's configure environment
confd -onetime -backend env
waitforit -address=$FRONTEND_API_URL/manage/availability -timeout=$TIMEOUT -insecure

cd /var/www
mkdir -p /tmp/behat
apt-get update > /dev/null 2>&1
export PGHOST=${DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${DATABASE_PASSWORD:=api}
export PGDATABASE=${DATABASE_NAME:=api}
export PGUSER=${DATABASE_USERNAME:=api}
rm -rf var/cache/*
# remove behat cache as it's mounted in a persistent container
rm -rf /tmp/behat/*

bin/behat --config=tests/behat/behat.yml --suite=admin --profile=${PROFILE:=headless} --stop-on-failure
