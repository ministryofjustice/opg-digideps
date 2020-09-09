TFLINT := tflint
TF := terraform
GS := git-secrets
APP_CONTAINERS := frontend api admin

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
	docker-compose up -d

up-app-dev: up-app dev-mode ## Brings the app up in dev mode

up-app-prod: up-app	prod-mode ## Brings the app up in dev mode

up-app-build: ## Brings the app up and rebuilds containers
	docker-compose up -d --build

up-app-xdebug-client: ## Brings the app up, rebuilds containers and enabled xdebug in client
	REQUIRE_XDEBUG_FRONTEND=true docker-compose up -d --build

up-app-xdebug-api: ## Brings the app up, rebuilds containers and enabled xdebug in client
	REQUIRE_XDEBUG_API=true docker-compose up -d --build

up-app-integration-tests: ## Brings the app up using test env vars (see test.env)
	docker-compose build frontend admin api localstack-init

	docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d

down-app: ### Tears down the app
	docker-compose down -v --remove-orphans

client-unit-tests: prod-mode ## Run the client unit tests
	docker-compose build frontend admin
	docker-compose -f docker-compose.yml run --rm frontend bin/phpunit -c tests/phpunit

api-unit-tests: reset-fixtures prod-mode ## Run the api unit tests
	docker-compose build api localstack-init
	docker-compose -f docker-compose.yml run --rm api sh scripts/apiunittest.sh

behat-tests: up-app-integration-tests reset-fixtures prod-mode
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test

behat-suite: up-app-integration-tests reset-fixtures prod-mode ## Pass in suite name as arg e.g. make behat-suite suite=<SUITE NAME>
	docker-compose -f docker-compose.yml -f docker-compose.dev.yml run --rm test --suite $(suite)

reset-database: ## Resets the DB schema and runs migrations
	docker-compose -f docker-compose.yml run --rm api sh scripts/reset_db_structure.sh

reset-fixtures: ## Resets the DB contents and reloads fixtures
	docker-compose -f docker-compose.yml run --rm api sh scripts/reset_db_fixtures.sh

prod-mode: ## Activates prod mode
	for c in ${APP_CONTAINERS} ; do \
	  docker-compose exec $$c touch /var/www/.enableProdMode ; \
	  echo "$$c: prod mode enabled." ; \
	done

dev-mode: ## Activates dev mode
	for c in ${APP_CONTAINERS} ; do \
	  docker-compose exec $$c rm -f /var/www/.enableProdMode ; \
	  echo "$$c: dev mode enabled." ; \
	done
