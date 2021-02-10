TFLINT := tflint
TF := terraform
GS := git-secrets
APP_CONTAINERS := frontend api admin
REDIS_CONTAINERS := redis-frontend redis-api

.ONESHELL:
.SHELL := /usr/bin/bash
.PHONY: checks check-compose check-terraform check-secrets lint-terraform

# Preflight checks
checks: check-compose check-terraform check-secrets lint-terraform

# Check compose file is valid
check-compose:
	@echo "Validating docker-compose"
	@docker-compose config -q

# Check for secrets or sensitive strings
check-secrets:
	@echo "Checking for secrets or sensitive strings"
	@$(GS) --scan --recursive

# Check terraform files are formatted correctly
check-terraform:
	@echo "Checking terraform format"
	@$(TF) fmt -recursive -diff=true

# Lint terraform
lint-terraform:
	@echo "Checking with tflint"
	@$(TFLINT) environment
	@$(TFLINT) shared

# DOCKER TASKS
up-app: ## Brings the app up
	docker-compose up -d --remove-orphans

up-app-build: ## Brings the app up and rebuilds containers
	COMPOSE_HTTP_TIMEOUT=90 docker-compose up -d --build --remove-orphans

up-app-xdebug-frontend: ## Brings the app up, rebuilds containers and enabled xdebug in client
	REQUIRE_XDEBUG_FRONTEND=true docker-compose up -d --build --remove-orphans

up-app-xdebug-frontend-cachegrind: ## Brings the app up, rebuilds containers and enabled xdebug in client with cachegrind being captured
 	REQUIRE_XDEBUG_FRONTEND=true docker-compose -f docker-compose.yml -f docker-compose.cachegrind.yml up -d --build --remove-orphans

up-app-xdebug-api: ## Brings the app up, rebuilds containers and enabled xdebug in client
	REQUIRE_XDEBUG_API=true docker-compose up -d --build --remove-orphans

up-app-xdebug-api-cachegrind: ## Brings the app up, rebuilds containers and enabled xdebug in client with cachegrind
	REQUIRE_XDEBUG_API=true docker-compose -f docker-compose.yml -f docker-compose.cachegrind.yml  up -d --build --remove-orphans


up-app-integration-tests: ## Brings the app up using test env vars (see test.env)
	REQUIRE_XDEBUG_FRONTEND=false REQUIRE_XDEBUG_API=false docker-compose -f docker-compose.yml -f docker-compose.dev.yml build frontend admin api
	APP_ENV=dev APP_DEBUG=0 docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d --remove-orphans

down-app: ### Tears down the app
	docker-compose down -v --remove-orphans

client-unit-tests: ## Run the client unit tests
	REQUIRE_XDEBUG_FRONTEND=false REQUIRE_XDEBUG_API=false docker-compose build frontend admin
	docker-compose -f docker-compose.yml run -e APP_ENV=unit_test -e APP_DEBUG=0 --rm frontend bin/phpunit -c tests/phpunit

api-unit-tests: reset-database reset-fixtures ## Run the api unit tests
	REQUIRE_XDEBUG_FRONTEND=false REQUIRE_XDEBUG_API=false docker-compose build api
	docker-compose -f docker-compose.yml run --rm -e APP_ENV=test -e APP_DEBUG=0 api sh scripts/apiunittest.sh

behat-tests: up-app-integration-tests reset-fixtures
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test

behat-suite: up-app-integration-tests reset-fixtures ## Pass in suite name as arg e.g. make behat-suite suite=<SUITE NAME>
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test --suite $(suite)

behat-profile-suite: up-app-integration-tests reset-fixtures prod-mode ## Pass in profile and suite name as args e.g. make behat-profile-suite profile=<PROFILE NAME> suite=<SUITE NAME>
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test --profile $(profile) --suite $(suite)

reset-database: ## Resets the DB schema and runs migrations
	docker-compose run --rm api sh scripts/reset_db_structure_local.sh

reset-fixtures: ## Resets the DB contents and reloads fixtures
	docker-compose run --rm api sh scripts/reset_db_fixtures_local.sh

redis-clear: ## Clears out all the data from redis (session related tokens)
	for c in ${REDIS_CONTAINERS} ; do \
	  docker-compose exec $$c redis-cli flushall; \
	  echo "$$c: redis cleared." ; \
	done

disable-debug: ## Puts app in dev mode and disables debug (so the app runs faster, but no toolbar/profiling)
	for c in ${APP_CONTAINERS} ; do \
	  APP_ENV=dev APP_DEBUG=0 docker-compose up -d --no-deps $$c; \
	  echo "$$c: debug disabled." ; \
	done

enable-debug: ## Puts app in dev mode and enables debug (so the app has toolbar/profiling)
	for c in ${APP_CONTAINERS} ; do \
	  APP_ENV=dev APP_DEBUG=1 docker-compose up -d --no-deps $$c; \
	  echo "$$c: debug enabled." ; \
	done
