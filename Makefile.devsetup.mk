precommit-install-mac:
	brew install pre-commit

composer-install:
	(cd ./common && composer update)
	(cd ./api/app && composer install)
	(cd ./client/app && composer install)
	composer update

precommit-setup:
	pre-commit install

setup-mac-development-tools: precommit-install-mac composer-install precommit-setup
