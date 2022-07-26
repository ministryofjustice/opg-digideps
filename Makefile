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

TFLINT := tflint
TF := terraform
GS := git-secrets
APP_CONTAINERS := frontend api admin
REDIS_CONTAINERS := redis-frontend redis-api

.ONESHELL:
.SHELL := /usr/bin/bash
.PHONY: checks check-compose check-terraform check-secrets lint-terraform help

checks: ##@checks Preflight checks
	check-compose check-terraform check-secrets lint-terraform

check-compose: ##@checks Check compose file is valid
	@echo "Validating docker-compose"
	@docker-compose config -q

check-secrets: ##@checks Check for secrets or sensitive strings
	@echo "Checking for secrets or sensitive strings"
	@$(GS) --scan --recursive

check-terraform: ##@checks Check terraform files are formatted correctly
	@echo "Checking terraform format"
	@$(TF) fmt -recursive -diff=true

lint-terraform: ##@checks Lint Terraform
	@echo "Checking with tflint"
	@$(TFLINT) environment
	@$(TFLINT) shared

up-app: ##@application Brings the app up
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d --remove-orphans

up-app-build: ##@application Brings the app up and rebuilds containers
	COMPOSE_HTTP_TIMEOUT=90 docker-compose up -d --build --remove-orphans

up-app-xdebug: ##@application Brings the app up, rebuilds containers and enabled xdebug in api and client (see DEBUGGING.md for config and setup)
	REQUIRE_XDEBUG_CLIENT=1 REQUIRE_XDEBUG_API=1 XDEBUG_IDEKEY_API=PHPSTORM-API XDEBUG_IDEKEY_CLIENT=PHPSTORM-CLIENT docker-compose up -d --build --remove-orphans

up-app-xdebug-client: ##@application Brings the app up, rebuilds containers and enabled xdebug in client
	REQUIRE_XDEBUG_CLIENT=1 XDEBUG_IDEKEY_CLIENT=PHPSTORM docker-compose up -d --build --remove-orphans

up-app-xdebug-client-cachegrind: ##@application Brings the app up, rebuilds containers and enabled xdebug in client with cachegrind being captured
 	REQUIRE_XDEBUG_CLIENT=1 XDEBUG_IDEKEY_CLIENT=PHPSTORM docker-compose -f docker-compose.yml -f docker-compose.cachegrind.yml up -d --build --remove-orphans

up-app-xdebug-api: ##@application Brings the app up, rebuilds containers and enabled xdebug in client
	REQUIRE_XDEBUG_API=1 XDEBUG_IDEKEY_API=PHPSTORM docker-compose up -d --build --remove-orphans

up-app-xdebug-api-cachegrind: ##@application Brings the app up, rebuilds containers and enabled xdebug in client with cachegrind
	REQUIRE_XDEBUG_API=1 XDEBUG_IDEKEY_API=PHPSTORM docker-compose -f docker-compose.yml -f docker-compose.cachegrind.yml  up -d --build --remove-orphans

up-app-integration-tests: ##@application Brings the app up using test env vars (see test.env)
	REQUIRE_XDEBUG_CLIENT=0 REQUIRE_XDEBUG_API=0 docker-compose -f docker-compose.yml -f docker-compose.dev.yml build frontend admin api test
	APP_DEBUG=0 docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d --remove-orphans

down-app: ##@application Tears down the app
	docker-compose down -v --remove-orphans

client-unit-tests: ##@unit-tests Run the client unit tests
	REQUIRE_XDEBUG_CLIENT=0 REQUIRE_XDEBUG_API=0 docker-compose build frontend admin
	docker-compose -f docker-compose.yml run -e APP_ENV=unit_test -e APP_DEBUG=0 --rm frontend vendor/bin/phpunit -c tests/phpunit

api-unit-tests: reset-fixtures ##@unit-tests Run the api unit tests
	REQUIRE_XDEBUG_CLIENT=0 REQUIRE_XDEBUG_API=0 docker-compose build api
	docker-compose -f docker-compose.yml run --rm -e APP_ENV=test -e APP_DEBUG=0 api sh scripts/apiunittest.sh

behat-tests: up-app-integration-tests reset-fixtures ##@behat Run the whole behat test suite
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test sh ./tests/Behat/run-tests.sh

behat-tests-tag: up-app-integration-tests reset-fixtures ##@behat Run behat tests with specified tag e.g. make behat-tests-tag tag=<tag> (Do not include the @)
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test sh ./tests/Behat/run-tests.sh --profile v2-tests-goutte --tags @$(tag)

behat-tests-v2-goutte: up-app-integration-tests reset-fixtures disable-debug ##@behat Pass in suite name as arg e.g. make behat-tests-v2-goutte suite=<SUITE NAME>
ifdef suite
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test sh ./tests/Behat/run-tests.sh --profile v2-tests-goutte --tags @v2 --suite $(suite)
else
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test sh ./tests/Behat/run-tests.sh --profile v2-tests-goutte --tags @v2
endif

behat-tests-v2-goutte-parallel: up-app-integration-tests reset-fixtures disable-debug ##@behat Run the integration tests in parallel
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test sh ./tests/Behat/run-tests.sh --tags @v2_sequential
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test sh ./tests/Behat/run-tests-parallel.sh --tags "@v2&&~@v2_sequential"

behat-tests-v2-browserstack: up-app-integration-tests reset-fixtures disable-debug
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test sh ./tests/Behat/run-tests.sh --profile v2-tests-browserstack --tags @v2

behat-suite: up-app-integration-tests reset-fixtures ##@behat Pass in suite name as arg e.g. make behat-suite suite=<SUITE NAME>
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test sh ./tests/Behat/run-tests.sh --suite $(suite)

behat-profile-suite: up-app-integration-tests reset-fixtures disable-debug ##@behat Pass in profile and suite name as args e.g. make behat-profile-suite profile=<PROFILE NAME> suite=<SUITE NAME>
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test sh ./tests/Behat/run-tests.sh --profile $(profile) --suite $(suite)

reset-database: ##@database Resets the DB schema and runs migrations
	docker-compose run --rm api sh scripts/reset_db_structure_local.sh

reset-fixtures: ##@database Resets the DB contents and reloads fixtures
	docker-compose run --rm api sh scripts/reset_db_fixtures_local.sh

db-terminal: ##@database Login to the database via the terminal
	docker-compose exec -it postgres sh -c "psql -U api"

api-logs: ##@logs Follow the API logs
	docker-compose logs api --follow

frontend-logs: ##@logs Follow the frontend logs
	docker-compose logs frontend --follow

admin-logs: ##@logs Follow the admin logs
	docker-compose logs admin --follow

redis-clear: ##@database Clears out all the data from redis (session related tokens)
	for c in ${REDIS_CONTAINERS} ; do \
	  docker-compose exec $$c redis-cli flushall; \
	  echo "$$c: redis cleared." ; \
	done

disable-debug: ##@application Puts app in dev mode and disables debug (so the app runs faster, but no toolbar/profiling)
	for c in ${APP_CONTAINERS} ; do \
	  APP_ENV=dev APP_DEBUG=0 docker-compose up -d --no-deps $$c; \
	  echo "$$c: debug disabled." ; \
	done

cache-clear: ##@application Clear the cache of the application
	docker-compose exec api sh -c "rm -rf var/cache/*" && \
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml exec api sh -c "rm -rf var/cache/*" && \
	docker-compose exec frontend sh -c "rm -rf var/cache/*" && \
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml exec frontend sh -c "rm -rf var/cache/*" && \
	docker-compose exec admin sh -c "rm -rf var/cache/*" && \
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml exec admin sh -c "rm -rf var/cache/*" && \
	echo "Cache reset"

enable-debug: ##@application Puts app in dev mode and enables debug (so the app has toolbar/profiling)
	for c in ${APP_CONTAINERS} ; do \
	  APP_ENV=dev APP_DEBUG=1 docker-compose up -d --no-deps $$c; \
	  echo "$$c: debug enabled." ; \
	done

phpstan-api: ##@static-analysis Runs PHPStan against API. Defaults to max level but supports passing level as an arg e.g. level=1
ifdef level
	docker-compose run --rm api vendor/phpstan/phpstan/phpstan analyse src --memory-limit=1G --level=$(level)
else
	docker-compose run --rm api vendor/phpstan/phpstan/phpstan analyse src --memory-limit=1G --level=max
endif


phpstan-client: ##@static-analysis Runs PHPStan against client. Defaults to max level but supports passing level as an arg e.g. level=1
ifdef level
	docker-compose run --rm frontend vendor/phpstan/phpstan/phpstan analyse src --memory-limit=1G --level=$(level)
else
	docker-compose run --rm frontend vendor/phpstan/phpstan/phpstan analyse src --memory-limit=1G --level=max
endif

get-audit-logs: ##@localstack Get audit log groups by passing event name e.g. get-audit-logs event_name=ROLE_CHANGED (see client/Audit/src/service/Audit/AuditEvents)
	docker-compose exec localstack awslocal logs get-log-events --log-group-name audit-local --log-stream-name $(event_name)

composer-api: ##@application Drops you into the API container with composer installed
	docker-compose exec api sh install-composer.sh
	docker-compose exec api sh

composer-client: ##@application Drops you into the frontend container with composer installed
	docker-compose exec frontend sh install-composer.sh
	docker-compose exec frontend sh
