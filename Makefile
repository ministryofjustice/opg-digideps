include Makefile.checks.mk
include Makefile.devsetup.mk
#COLORS
GREEN  := $(shell tput -Txterm setaf 2)
WHITE  := $(shell tput -Txterm setaf 7)
YELLOW := $(shell tput -Txterm setaf 3)
RESET  := $(shell tput -Txterm sgr0)

# Add the following 'help' target to your Makefile
# And add help text after each target name starting with '\#\#'
# A category can be added with @category
# This was made possible by https://gist.github.com/prwhite/8168133#gistcomment-1727513
HELP_FUN = \
    %help; \
    while(<>) { push @{$$help{$$2 // 'options'}}, [$$1, $$3] if /^([a-zA-Z0-9\-]+)\s*:.*\#\#(?:@([a-zA-Z\-]+))?\s(.*)$$/ }; \
    print "usage: make [target]\n\n"; \
    for (sort keys %help) { \
    print "${WHITE}$$_:${RESET}\n"; \
    for (@{$$help{$$_}}) { \
    $$sep = " " x (32 - length $$_->[0]); \
    print "  ${YELLOW}$$_->[0]${RESET}$$sep${GREEN}$$_->[1]${RESET}\n"; \
    }; \
    print "\n"; }

help: ##@other Show this help.
	@perl -e '$(HELP_FUN)' $(MAKEFILE_LIST)

APP_CONTAINERS := frontend-app api-app admin-app
REDIS_CONTAINERS := redis-frontend redis-api

.ONESHELL:
.SHELL := /usr/bin/bash

create-app: build-app up-app reset-database reset-fixtures ##@application Brings up app with DB reset and a full no-cache build

build-app: down-app build-js ##@application Brings up app with a full no-cache build
	docker container prune --force
	docker compose build --no-cache

ADDITIONAL_CONFIG = -f docker-compose.override.yml

up-app: ##@application Brings the app up and mounts local folders
	COMPOSE_HTTP_TIMEOUT=90 docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} up -d --remove-orphans load-balancer

up-app-rebuild: build-js ##@application Brings up app with a basic rebuild
	docker compose down
	docker container prune --force
	COMPOSE_HTTP_TIMEOUT=90 docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} up -d --remove-orphans --build load-balancer
	$(MAKE) cache-clear

up-app-xdebug: ##@application Brings the app up, rebuilds containers and enabled xdebug in api and client (see DEBUGGING.md for config and setup)
	REQUIRE_XDEBUG_CLIENT=1 REQUIRE_XDEBUG_API=1 XDEBUG_IDEKEY_API=PHPSTORM-API XDEBUG_IDEKEY_CLIENT=PHPSTORM-CLIENT docker compose up -d --build --remove-orphans load-balancer

up-app-xdebug-client: ##@application Brings the app up, rebuilds containers and enabled xdebug in client
	REQUIRE_XDEBUG_CLIENT=1 XDEBUG_IDEKEY_CLIENT=PHPSTORM docker compose up -d --build --remove-orphans load-balancer

up-app-xdebug-client-cachegrind: ##@application Brings the app up, rebuilds containers and enabled xdebug in client with cachegrind being captured
 	REQUIRE_XDEBUG_CLIENT=1 XDEBUG_IDEKEY_CLIENT=PHPSTORM docker compose -f docker-compose.yml -f docker-compose.cachegrind.yml up -d --build --remove-orphans load-balancer

up-app-xdebug-api: ##@application Brings the app up, rebuilds containers and enabled xdebug in client
	REQUIRE_XDEBUG_API=1 XDEBUG_IDEKEY_API=PHPSTORM docker compose up -d --build --remove-orphans load-balancer

up-app-xdebug-api-cachegrind: ##@application Brings the app up, rebuilds containers and enabled xdebug in client with cachegrind
	REQUIRE_XDEBUG_API=1 XDEBUG_IDEKEY_API=PHPSTORM docker compose -f docker-compose.yml -f docker-compose.cachegrind.yml up -d --build --remove-orphans load-balancer

down-app: ##@application Tears down the app
	docker compose down -v --remove-orphans

tag := "v2"
end-to-end-tests: up-app reset-database ##@end-to-end-tests Brings the app up using test env vars (see test.env)
	APP_DEBUG=0 docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} run --rm end-to-end-tests sh ./tests/Behat/run-tests.sh --tags @$(tag)

end-to-end-tests-parallel: ##@end-to-end-tests Rerun the end to end tests in parallel (requires you to have run end-to-end-tests previously)
	APP_DEBUG=0 docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} run --rm end-to-end-tests sh ./tests/Behat/run-tests.sh --tags @v2_sequential
	APP_DEBUG=0 docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} run --rm end-to-end-tests sh ./tests/Behat/run-tests-parallel.sh --tags "@v2&&~@v2_sequential"

end-to-end-tests-browserkit: ##@end-to-end-tests Pass in suite name as arg e.g. make behat-tests-v2-browserkit suite=<SUITE NAME>

ifdef suite
	APP_DEBUG=0 docker compose -f docker-compose.yml -f docker-compose.override.yml run -rm end-to-end-tests sh ./tests/Behat/run-tests.sh --profile v2-tests-browserkit --tags @v2 --suite $(suite)
else
	APP_DEBUG=0 docker compose -f docker-compose.yml -f docker-compose.override.yml run -rm end-to-end-tests sh ./tests/Behat/run-tests.sh --profile v2-tests-browserkit --tags @v2
endif

client-unit-tests: ##@unit-tests Run the client unit tests
	docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} run -e APP_ENV=dev -e APP_DEBUG=0 --rm client-unit-tests sh scripts/client-unit-tests.sh cov-html

api-unit-tests: ##@unit-tests Run the api unit tests
	docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} run -e APP_ENV=test -e APP_DEBUG=0 --rm api-unit-tests sh scripts/api_unit_test.sh cov-html

INTEGRATION_SELECTION := selection-all
api-integration-tests: reset-database-integration-tests ##@integration-tests Run the api integration tests
	docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} run -e APP_ENV=test -e APP_DEBUG=0 --rm api-integration-tests sh scripts/api_integration_test.sh ${INTEGRATION_SELECTION}

api-integration-tests-solo: reset-database-integration-tests ##@integration-tests Run individual api integration test
#Example command: make api-integration-test-solo suite=Controller/AuthControllerTest.php test_case=testLoginFailWrongPassword (test case argument is optional)
	docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} run -e APP_ENV=test -e APP_DEBUG=0 --rm api-integration-tests sh scripts/api_integration_test.sh selection-solo $(suite) $(test_case)

reset-database-integration-tests: ##@database Resets the DB schema and runs migrations
	docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} run --rm api-integration-tests sh scripts/reset_db_structure.sh local

reset-database: ##@database Resets the DB schema and runs migrations
	docker compose run --rm api-app sh scripts/reset_db_structure.sh local

reset-fixtures: ##@database Resets the DB contents and reloads fixtures
	docker compose run --rm api-app sh scripts/reset_db_fixtures.sh local

db-terminal: ##@database Login to the database via the terminal
	docker compose exec -it postgres sh -c "psql -U api"

api-logs: ##@logs Follow the API logs
	docker compose logs api-webserver api-app --follow

front-logs: ##@logs Follow the frontend logs
	docker compose logs frontend-webserver frontend-app --follow

admin-logs: ##@logs Follow the admin logs
	docker compose logs admin-webserver admin-app --follow

redis-clear: ##@database Clears out all the data from redis (session related tokens)
	for c in ${REDIS_CONTAINERS} ; do \
	  docker compose exec $$c redis-cli flushall; \
	  echo "$$c: redis cleared." ; \
	done

cache-clear: ##@application Clear the cache of the application
	docker compose exec api-app sh -c "rm -rf var/cache/*" && \
	docker compose exec frontend-app sh -c "rm -rf var/cache/*" && \
	docker compose exec admin-app sh -c "rm -rf var/cache/*" && \
	echo "Cache reset"

enable-debug: ##@application Puts app in dev mode and enables debug (so the app has toolbar/profiling)
	for c in ${APP_CONTAINERS} ; do \
	  APP_ENV=dev APP_DEBUG=1 docker compose up -d --no-deps $$c; \
	  echo "$$c: debug enabled." ; \
	done

disable-debug: ##@application Puts app in dev mode and disables debug (so the app runs faster, but no toolbar/profiling)
	for c in ${APP_CONTAINERS} ; do \
	  APP_ENV=dev APP_DEBUG=0 docker compose up -d --no-deps $$c; \
	  echo "$$c: debug disabled." ; \
	done

PHPSTAN-LEVEL := max
phpstan-api: ##@static-analysis Runs PHPStan against API. Defaults to max level but supports passing level as an arg e.g. level=1
	docker compose -f docker-compose.yml -f docker-compose.override.yml run --no-deps --rm api-app vendor/phpstan/phpstan/phpstan analyse src --memory-limit=1G --level=$(PHPSTAN-LEVEL)

phpstan-api-baseline:
	docker compose -f docker-compose.yml -f docker-compose.override.yml run --no-deps --rm api-app vendor/phpstan/phpstan/phpstan analyse src --memory-limit=1G --level=max --generate-baseline

phpstan-client: ##@static-analysis Runs PHPStan against client. Defaults to max level but supports passing level as an arg e.g. level=1
	docker compose -f docker-compose.yml -f docker-compose.override.yml run --no-deps --rm frontend-app vendor/phpstan/phpstan/phpstan analyse src --memory-limit=1G --level=$(PHPSTAN-LEVEL)

phpstan-client-baseline:
	docker compose -f docker-compose.yml -f docker-compose.override.yml run --no-deps --rm frontend-app vendor/phpstan/phpstan/phpstan analyse src --memory-limit=1G --level=max --generate-baseline

get-audit-logs: ##@localstack Get audit log groups by passing event name e.g. get-audit-logs event_name=ROLE_CHANGED (see client/Audit/src/service/Audit/AuditEvents)
	docker compose exec localstack awslocal logs get-log-events --log-group-name audit-local --log-stream-name $(event_name)

composer-api: ##@application Drops you into the API container with composer installed
	docker compose run --rm --volume ~/.composer:/tmp --volume ${PWD}/api/app:/app composer ${COMPOSER_ARGS}

composer-client: ##@application Drops you into the frontend container with composer installed
	docker compose run --rm --volume ~/.composer:/tmp --volume ${PWD}/client/app:/app composer ${COMPOSER_ARGS}

js-lint: ##@javascript Lint JS resources
	docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} run --rm node-js npm run lint

js-fix: ##@javascript Automatically fix JS linting issues
	docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} run --rm node-js npm run fix

js-test: ##@javascript Run JS tests
	docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} run --rm node-js npm run test

TEST:='all'
js-test-single: ##@javascript Allows you to do make js-test-single TEST='Currency Formatting' to run tests whose describe matches the string
	docker compose -f docker-compose.yml ${ADDITIONAL_CONFIG} run --rm node-js npm run test -- -t ${TEST}

build-js: ##@javascript Build JS resources
	docker compose build resources --no-cache
	docker compose up resources

zap-admin: up-app reset-database reset-fixtures ##@zap Run ZAP against local admin
	docker compose -f docker-compose.yml -f docker-compose.commands.yml up zap-admin

zap-front: up-app reset-database reset-fixtures ##@zap Run ZAP against local frontend
	docker compose -f docker-compose.yml -f docker-compose.commands.yml up zap-front

smoke-tests: ##@smoke-tests Run smoke tests (requires app to be up)
	docker compose build orchestration
	docker compose run --remove-orphans orchestration sh tests/run-smoke-tests.sh

smoke-tests-local: up-app reset-database reset-fixtures ##@smoke-tests Run smoke tests (requires app to be up)
	docker compose build orchestration
	docker compose run -e ENVIRONMENT=local --remove-orphans orchestration sh tests/run-smoke-tests.sh

resilience-tests: ##@resilience-tests Run resilience tests (requires app to be up)
	docker compose build orchestration
	docker compose run -e LOG_AND_CONTINUE=true --remove-orphans orchestration sh tests/run-resilience-tests.sh

sql-custom-command-insert: ##@sql-custom-command Run SQL insert custom command
	docker compose -f docker-compose.commands.yml build sql-custom-command
	docker compose -f docker-compose.commands.yml run --remove-orphans sql-custom-command $(workspace) insert --sql_file=_run.sql --verification_sql_file=_verification.sql --expected_before=$(before) --expected_after=$(after) --maximum_rows_affected=$(max)

sql-custom-command-get: ##@sql-custom-command Run SQL get custom command
	docker compose -f docker-compose.commands.yml build sql-custom-command
	docker compose -f docker-compose.commands.yml run --remove-orphans sql-custom-command $(workspace) get --query_id=$(id)

sql-custom-command-sign-off: ##@sql-custom-command Run SQL sign off custom command
	docker compose -f docker-compose.commands.yml build sql-custom-command
	docker compose -f docker-compose.commands.yml run --remove-orphans sql-custom-command $(workspace) sign_off --query_id=$(id)

sql-custom-command-execute: ##@sql-custom-command Run SQL execute custom command
	docker compose -f docker-compose.commands.yml build sql-custom-command
	docker compose -f docker-compose.commands.yml run --remove-orphans sql-custom-command $(workspace) execute --query_id=$(id)

sql-custom-command-revoke: ##@sql-custom-command Run SQL revoke custom command
	docker compose -f docker-compose.commands.yml build sql-custom-command
	docker compose -f docker-compose.commands.yml run --remove-orphans sql-custom-command $(workspace) revoke --query_id=$(id)

set-feature-flag: ##@localstack Set a particular feature flags value e.g. set-feature-flag name=multi-accounts value=1
	docker compose exec localstack awslocal ssm put-parameter --name "/local/flag/$(name)" --value "$(value)" --type String --overwrite
