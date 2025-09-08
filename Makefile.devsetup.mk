precommit-install-mac:
	brew install pre-commit

precommit-download-requirements:
	composer install

precommit-setup:
	pre-commit install

setup-mac-development-tools: precommit-install-mac precommit-download-requirements precommit-setup
