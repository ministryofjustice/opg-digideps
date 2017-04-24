#!/bin/bash
bash scripts/configureEnvironment.sh

export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=api}
export PGUSER=${API_DATABASE_USERNAME:=api}

# phpunit
#/sbin/setuser app php vendor/phpunit/phpunit/phpunit -c tests/phpunit/ #Unit tests. Initial run can take a significant amount of time but subsequent runs are <10 seconds

# behat
export BEHAT_PARAMS="{\"extensions\" : {\"Behat\\\\MinkExtension\\\\ServiceContainer\\\\MinkExtension\" : {\"base_url\" : \"${FRONTEND_NONADMIN_HOST}\",\"selenium2\" : { \"wd_host\" : \"$WD_HOST\" }, \"browser_stack\" : { \"username\": \"$BROWSERSTACK_USER\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
#/sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=deputy --profile=${PROFILE:=headless}
#/sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=deputyodr --profile=${PROFILE:=headless}
#/sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=pa --profile=${PROFILE:=headless}
#/sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=pa --profile=${PROFILE:=headless} tests/behat/features/pa/01-admin-add-pa-users-and-activate.feature
#/sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=pa --profile=${PROFILE:=headless} tests/behat/features/pa/02-dashboard.feature
/sbin/setuser app bin/behat --config=tests/behat/behat.yml --suite=pa --profile=${PROFILE:=headless} --tags pateam
