## Xdebug

To use Xdebug in the `frontend` and/or `admin` app, it must be installed on the client image. To install, you will need to either manually pass `REQUIRE_XDEBUG_FRONTEND=1` as an env var when bringing up the app or run the make command `up-app-xdebug-frontend`

To install Xdebug on the API, either manually pass `REQUIRE_XDEBUG_API=1` as an env var when bringing up the app or run the make command `up-app-xdebug-api`.

You can confirm installation by running `php -v` in the container and seeing that it reports the Xdebug version.

Since v3 performance with Xdebug enabled is only slightly slower than without so leaving Xdebug enabled shouldn't be a huge performance hit compared with previous versions.

## PHPStorm PHPUnit XDebug setup

To enable step through xdebug in PHPStorm/IntelliJ you'll need to ensure the setup for PHP and PHPUnit are pointing to the relevant Docker PHP install.

### Create a Docker PHP CLI Interpreter
The steps below are for setting this up for the API container. This will need to be followed again for setting up Client/Admin with any api specific namings or file locations updated to match the client directory.

Go to Settings > Languages & Frameworks > PHP > Click `...` next to CLI Interpreter.

Add a new interpreter with `+` and select `From Docker, Vagrant, Vm, Remote...`. Choose `Docker Compose`, ensure the root docker-compose.yml file is selected for Configuration file. Give this a sensible name that includes `api`.

Back in Languages & Frameworks > PHP > Debug ensure the port in the Xdebug section is set to `9003`.

In Languages & Frameworks > PHP > Test Frameworks add a new configuration type and select `PHP Unit by Remote Interpreter` and select the CLI Interpreter that was just created for Docker api. PHP Unit Library > select `Use Composer Autoloader` and enter `/var/www/vendor/autoload.php` in `Path to script`. Click the refresh symbol and confirm a version of PHP Unit is displayed here - if not then recheck the path mappings set up above. For `Default Configuration File` enter `/var/www/tests/phpunit/phpunit.xml` and  `Default Bootstrap File` enter `/var/www/tests/phpunit/bootstrap.php`.

This should be everything required to have debugging set up for test runs. To confirm, find a test and add a breakpoint in the test itself or a class that is exercised during the test then click the play symbol next to the test function and hit the debug icon. Once the container starts up you should drop into debug mode and have the ability to step through the code and execute commands against the current state of the app.
