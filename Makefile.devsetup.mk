precommit-install-mac:
	brew install pre-commit

composer-install:
	(cd ./common && composer install)
	(cd ./api/app && composer install)
	(cd ./client/app && composer install)
	composer install

precommit-setup:
	pre-commit install

setup-mac-development-tools: precommit-install-mac composer-install precommit-setup
