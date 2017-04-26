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
export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=api}
export PGUSER=${API_DATABASE_USERNAME:=api}
rm -rf app/cache/*

# phpunit
/sbin/setuser app php vendor/phpunit/phpunit/phpunit -c tests/phpunit/

# behat
export BEHAT_PARAMS="{\"extensions\" : {\"Behat\\\\MinkExtension\\\\ServiceContainer\\\\MinkExtension\" : {\"base_url\" : \"${FRONTEND_NONADMIN_HOST}\",\"selenium2\" : { \"wd_host\" : \"$WD_HOST\" }, \"browser_stack\" : { \"username\": \"$BROWSERSTACK_USER\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
/sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=deputy --profile=${PROFILE:=headless}
/sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=deputyodr --profile=${PROFILE:=headless}
# /sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=pa --profile=${PROFILE:=headless}
