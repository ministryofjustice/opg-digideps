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

# behat
export BEHAT_PARAMS="{\"extensions\" : {\"Behat\\\\MinkExtension\\\\ServiceContainer\\\\MinkExtension\" : {\"base_url\" : \"${FRONTEND_NONADMIN_HOST}\",\"selenium2\" : { \"wd_host\" : \"$WD_HOST\" }, \"browser_stack\" : { \"username\": \"$BROWSERSTACK_USER\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
if [ -z "$2" ]; then
	/sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=$1 --profile=${PROFILE:=headless}
elif [ ! -z "$3" ]; then
# assume the presence of a third argument is a specific feature file
	/sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=$1 --profile=${PROFILE:=headless} $3
else
    /sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=$1 --profile=${PROFILE:=headless} --tags $2
fi