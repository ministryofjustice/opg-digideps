include Makefile.checks.mk
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
	docker-compose build --no-cache

up-app: ##@application Brings the app up and mounts local folders
	COMPOSE_HTTP_TIMEOUT=90 docker-compose up -d --remove-orphans load-balancer

up-app-rebuild: ##@application Brings up app with a basic rebuild
	docker-compose down
	docker container prune --force
	COMPOSE_HTTP_TIMEOUT=90 docker-compose up -d --remove-orphans --build load-balancer

up-app-xdebug: ##@application Brings the app up, rebuilds containers and enabled xdebug in api and client (see DEBUGGING.md for config and setup)
	REQUIRE_XDEBUG_CLIENT=1 REQUIRE_XDEBUG_API=1 XDEBUG_IDEKEY_API=PHPSTORM-API XDEBUG_IDEKEY_CLIENT=PHPSTORM-CLIENT docker-compose up -d --build --remove-orphans load-balancer

up-app-xdebug-client: ##@application Brings the app up, rebuilds containers and enabled xdebug in client
	REQUIRE_XDEBUG_CLIENT=1 XDEBUG_IDEKEY_CLIENT=PHPSTORM docker-compose up -d --build --remove-orphans load-balancer

up-app-xdebug-client-cachegrind: ##@application Brings the app up, rebuilds containers and enabled xdebug in client with cachegrind being captured
 	REQUIRE_XDEBUG_CLIENT=1 XDEBUG_IDEKEY_CLIENT=PHPSTORM docker-compose -f docker-compose.yml -f docker-compose.cachegrind.yml up -d --build --remove-orphans load-balancer

up-app-xdebug-api: ##@application Brings the app up, rebuilds containers and enabled xdebug in client
	REQUIRE_XDEBUG_API=1 XDEBUG_IDEKEY_API=PHPSTORM docker-compose up -d --build --remove-orphans load-balancer

up-app-xdebug-api-cachegrind: ##@application Brings the app up, rebuilds containers and enabled xdebug in client with cachegrind
	REQUIRE_XDEBUG_API=1 XDEBUG_IDEKEY_API=PHPSTORM docker-compose -f docker-compose.yml -f docker-compose.cachegrind.yml up -d --build --remove-orphans load-balancer

down-app: ##@application Tears down the app
	docker-compose down -v --remove-orphans

integration-tests: up-app reset-database reset-fixtures ##@integration-tests Brings the app up using test env vars (see test.env)
	REQUIRE_XDEBUG_CLIENT=0 REQUIRE_XDEBUG_API=0 docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose.override.yml build frontend-app frontend-webserver admin-app admin-webserver api-app integration-tests
	REQUIRE_XDEBUG_CLIENT=0 REQUIRE_XDEBUG_API=0 docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose.override.yml up -d load-balancer
	APP_DEBUG=0 docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose.override.yml run --remove-orphans integration-tests sh ./tests/Behat/run-tests.sh --tags @v2

integration-tests-rerun: reset-fixtures ##@integration-tests Rerun integration tests (requires you to have run integration-tests previously), argument in format: tag=your_tag
	APP_DEBUG=0 docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose.override.yml run --remove-orphans integration-tests sh ./tests/Behat/run-tests.sh --tags @v2

integration-tests-tag: reset-fixtures ##@integration-tests Rerun integration tests with a tag (requires you to have run integration-tests previously)
	APP_DEBUG=0 docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose.override.yml run --remove-orphans integration-tests sh ./tests/Behat/run-tests.sh --tags @$(tag)

integration-tests-parallel: reset-fixtures ##@integration-tests Rerun the integration tests in parallel (requires you to have run integration-tests previously)
	APP_DEBUG=0 docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose.override.yml run --remove-orphans integration-tests sh ./tests/Behat/run-tests.sh --tags @v2_sequential
	APP_DEBUG=0 docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose.override.yml run --remove-orphans integration-tests sh ./tests/Behat/run-tests-parallel.sh --tags "@v2&&~@v2_sequential"

integration-tests-browserkit: reset-fixtures ##@integration-tests Pass in suite name as arg e.g. make behat-tests-v2-browserkit suite=<SUITE NAME>
ifdef suite
	APP_DEBUG=0 docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose.override.yml run --remove-orphans integration-tests sh ./tests/Behat/run-tests.sh --profile v2-tests-browserkit --tags @v2 --suite $(suite)
else
	APP_DEBUG=0 docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose.override.yml run --remove-orphans integration-tests sh ./tests/Behat/run-tests.sh --profile v2-tests-browserkit --tags @v2
endif

client-unit-tests: ##@unit-tests Run the client unit tests
	REQUIRE_XDEBUG_CLIENT=0 REQUIRE_XDEBUG_API=0 docker-compose -f docker-compose.yml -f docker-compose.unit-tests-client.yml build client-unit-tests
	docker-compose -f docker-compose.yml -f docker-compose.unit-tests-client.yml up -d pact-mock
	sleep 5
	docker-compose -f docker-compose.yml -f docker-compose.unit-tests-client.yml run -e APP_ENV=dev -e APP_DEBUG=0 --rm client-unit-tests vendor/bin/phpunit -c tests/phpunit

api-unit-tests: reset-database-unit-tests reset-fixtures-unit-tests ##@unit-tests Run the api unit tests
	REQUIRE_XDEBUG_FRONTEND=0 REQUIRE_XDEBUG_API=0 docker-compose -f docker-compose.yml -f docker-compose.unit-tests-api.yml build api-unit-tests
	docker-compose -f docker-compose.yml -f docker-compose.unit-tests-api.yml run -e APP_ENV=test -e APP_DEBUG=0 --rm api-unit-tests sh scripts/api_unit_test.sh selection-all

reset-database-unit-tests: ##@database Resets the DB schema and runs migrations
	docker-compose -f docker-compose.yml -f docker-compose.unit-tests-api.yml run --rm api-unit-tests sh scripts/reset_db_structure_local.sh

reset-fixtures-unit-tests: ##@database Resets the DB schema and runs migrations
	docker-compose -f docker-compose.yml -f docker-compose.unit-tests-api.yml run --rm api-unit-tests sh scripts/reset_db_fixtures_local.sh

reset-database: ##@database Resets the DB schema and runs migrations
	docker-compose run --rm api-app sh scripts/reset_db_structure_local.sh

reset-fixtures: ##@database Resets the DB contents and reloads fixtures
	docker-compose run --rm api-app sh scripts/reset_db_fixtures_local.sh

db-terminal: ##@database Login to the database via the terminal
	docker-compose exec -it postgres sh -c "psql -U api"

api-logs: ##@logs Follow the API logs
	docker-compose logs api-webserver api-app --follow

front-logs: ##@logs Follow the frontend logs
	docker-compose logs frontend-webserver frontend-app --follow

admin-logs: ##@logs Follow the admin logs
	docker-compose logs admin-webserver admin-app --follow

redis-clear: ##@database Clears out all the data from redis (session related tokens)
	for c in ${REDIS_CONTAINERS} ; do \
	  docker-compose exec $$c redis-cli flushall; \
	  echo "$$c: redis cleared." ; \
	done

cache-clear: ##@application Clear the cache of the application
	docker-compose exec api-app sh -c "rm -rf var/cache/*" && \
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml exec api-app sh -c "rm -rf var/cache/*" && \
	docker-compose exec frontend-app sh -c "rm -rf var/cache/*" && \
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml exec frontend-app sh -c "rm -rf var/cache/*" && \
	docker-compose exec admin-app sh -c "rm -rf var/cache/*" && \
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml exec admin-app sh -c "rm -rf var/cache/*" && \
	echo "Cache reset"

enable-debug: ##@application Puts app in dev mode and enables debug (so the app has toolbar/profiling)
	for c in ${APP_CONTAINERS} ; do \
	  APP_ENV=dev APP_DEBUG=1 docker-compose up -d --no-deps $$c; \
	  echo "$$c: debug enabled." ; \
	done

disable-debug: ##@application Puts app in dev mode and disables debug (so the app runs faster, but no toolbar/profiling)
	for c in ${APP_CONTAINERS} ; do \
	  APP_ENV=dev APP_DEBUG=0 docker-compose up -d --no-deps $$c; \
	  echo "$$c: debug disabled." ; \
	done

phpstan-api: ##@static-analysis Runs PHPStan against API. Defaults to max level but supports passing level as an arg e.g. level=1
ifdef level
	docker-compose run --rm api-app vendor/phpstan/phpstan/phpstan analyse src --memory-limit=1G --level=$(level)
else
	docker-compose run --rm api-app vendor/phpstan/phpstan/phpstan analyse src --memory-limit=1G --level=max
endif

phpstan-client: ##@static-analysis Runs PHPStan against client. Defaults to max level but supports passing level as an arg e.g. level=1
ifdef level
	docker-compose run --rm frontend-app vendor/phpstan/phpstan/phpstan analyse src --memory-limit=1G --level=$(level)
else
	docker-compose run --rm frontend-app vendor/phpstan/phpstan/phpstan analyse src --memory-limit=1G --level=max
endif

get-audit-logs: ##@localstack Get audit log groups by passing event name e.g. get-audit-logs event_name=ROLE_CHANGED (see client/Audit/src/service/Audit/AuditEvents)
	docker-compose exec localstack awslocal logs get-log-events --log-group-name audit-local --log-stream-name $(event_name)

composer-api: ##@application Drops you into the API container with composer installed
	docker-compose run --rm --volume ~/.composer:/tmp --volume ${PWD}/api/app:/app composer ${COMPOSER_ARGS}

composer-client: ##@application Drops you into the frontend container with composer installed
	docker-compose run --rm --volume ~/.composer:/tmp --volume ${PWD}/client/app:/app composer ${COMPOSER_ARGS}

test-js: ##@javascript Run JS tests
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run node-js --build --rm run test

TEST:='all'
test-js-single: ##@javascript Allows you to do make test-js-single TEST='Currency Formatting' to run tests whose describe matches the string
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run node-js --build --rm run test -- -t ${TEST}

build-js: ##@javascript Build JS resources
	docker-compose run resources --build --rm

lint-js: ##@javascript Lint JS resources
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run node-js --build --rm run fix
