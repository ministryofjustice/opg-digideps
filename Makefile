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
