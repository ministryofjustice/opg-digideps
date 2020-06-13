TFLINT := tflint
TF := terraform
GS := git-secrets

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
up-app-dev: dev-mode ## Brings the app up in dev mode
	docker-compose up -d

up-app-prod: up-app-dev	prod-mode ## Brings the app up in dev mode

client-unit-tests: ## Run the client unit tests
	docker-compose -f docker-compose.yml run --rm frontend bin/phpunit -c tests/phpunit

api-unit-tests: reset-fixtures ## Run the api unit tests
	docker-compose -f docker-compose.yml run --rm api sh scripts/apiunittest.sh

behat-tests: reset-fixtures
	docker-compose -f docker-compose.dev.yml run --rm test

behat-suite: reset-fixtures ## Pass in suite name as arg e.g. make behat-suite suite=<SUITE NAME>
	docker-compose -f docker-compose.dev.yml run --rm test --suite $(suite)

reset-database:
	docker-compose -f docker-compose.yml run --rm api sh scripts/reset_db_structure.sh

reset-fixtures:
	docker-compose -f docker-compose.yml run --rm api sh scripts/reset_db_fixtures.sh

prod-mode:
	containers=(frontend api admin)
	for i in "${containers[@]}"
	do
	  docker-compose exec $i touch /var/www/.enableProdMode
	  echo "$i: prod mode enabled."
	done

dev-mode:
	containers=(frontend api admin)
	for i in "${containers[@]}"
	do
	  docker-compose exec $i rm -f /var/www/.enableProdMode
       echo "$i: dev mode."
	done
