#!/bin/bash
sh scripts/configureEnvironment.sh

# environment variables for psql command
export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=api}
export PGUSER=${API_DATABASE_USERNAME:=api}

# behat
export BEHAT_PARAMS="{\"extensions\" : {\"Behat\\\\MinkExtension\\\\ServiceContainer\\\\MinkExtension\" : {\"base_url\" : \"${FRONTEND_NONADMIN_HOST}\",\"selenium2\" : { \"wd_host\" : \"$WD_HOST\" }, \"browser_stack\" : { \"username\": \"$BROWSERSTACK_USER\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
if [ -z "$2" ]; then
	/sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=$1 --profile=${PROFILE:=headless}
else
    /sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=$1 --profile=${PROFILE:=headless} --tags $2
fi