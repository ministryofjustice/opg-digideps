TFLINT := tflint
TF := terraform
GS := git-secrets

.ONESHELL:
.SHELL := /usr/bin/bash
.PHONY: checks check-compose check-terraform check-secrets lint-terraform help

checks: check-compose check-terraform check-secrets lint-terraform ##@checks Preflight checks

check-compose: ##@checks Check compose file is valid
	@echo "Validating docker-compose"
	@docker compose config -q

check-secrets: ##@checks Check for secrets or sensitive strings
	@echo "Checking for secrets or sensitive strings"
	@$(GS) --scan --recursive

check-terraform: ##@checks Check terraform files are formatted correctly
	@echo "Checking terraform format"
	@$(TF) fmt -recursive -diff=true

lint-terraform: ##@checks Lint Terraform
	@echo "Checking with tflint"
	@$(TFLINT) --chdir environment
	@$(TFLINT) --chdir account
