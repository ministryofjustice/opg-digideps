SERVICES := scan-${TF_WORKSPACE} front-${TF_WORKSPACE} admin-${TF_WORKSPACE} wkhtmltopdf-${TF_WORKSPACE}

TFHOG := docker-compose run --rm trufflehog
TFLINT := docker-compose run --rm tflint
TF := docker-compose run --rm terraform
AWS := docker-compose run --rm aws
JQ := docker-compose run --rm jq
RUBY := docker-compose run --rm ruby

.ONESHELL:
.SHELL := /usr/bin/bash
.PHONY: style check-terraform check-secrets check-compose init deploy docs precommit reset-database test

# Build aws config file
aws-config:
	@cat terraform.tfvars | $(JQ) -rf aws.jq > .tmp.aws.config

# Check style and lint
style: check-compose check-terraform lint-terraform

# Check terraform files are formatted correctly
check-terraform:
	@echo "Checking terraform format"
	@$(TF) fmt -check=true -diff=true

# Check for secrets or sensitive strings
# NOTE: this is configured with the repo as parent dir in docker-compose
check-secrets:
	@echo "Checking for secrets or sensitive strings"
	@$(TFHOG)

# Check compose file is valid
check-compose:
	@echo "Validating docker-compose"
	@docker-compose config -q

# Lint terraform
lint-terraform:
	@$(TFLINT)

# Initialise terraform backend
init:
	@echo "Initialising terraform"
	@$(TF) init -input=false

# Apply terraform and wait for stable services
deploy: style
	@if ! [ -z ${JENKINS_HOME} ]; then \
		echo "Running in CI"; \
		echo "Applying terraform";  \
		$(TF) apply -input=false -auto-approve; \
	else \
		echo "Applying terraform";  \
		$(TF) apply -input=false; \
	fi

	@$(RUBY) deployer/check_deployment.rb ${SERVICES}

# Update readme to reflect terraform docs
docs:
	@echo "Updating TERRAFORM.md"
	@terraform-docs \
		--sort-inputs-by-required \
		--with-aggregate-type-defaults \
		md . > TERRAFORM.md

# Preflight checks for a commit
precommit: style docs

# ensure ruby is latest build
build-ruby:
	docker-compose build ruby

# reset database
reset-database: build-ruby
	@$(RUBY) deployer/run_task.rb test-${TF_WORKSPACE} reset-database-${TF_WORKSPACE}

# run test task
test: reset-database build-ruby
	@$(RUBY) deployer/run_task.rb test-${TF_WORKSPACE} test-api-${TF_WORKSPACE}
	@$(RUBY) deployer/run_task.rb test-${TF_WORKSPACE} test-front-${TF_WORKSPACE}
