## Xdebug

To use Xdebug in the `frontend` and/or `admin` app, it must be installed on the client image. To install, you will need to create a `.env` file in the top-level of this repo, and add the following:

```
REQUIRE_XDEBUG_API=0
REQUIRE_XDEBUG_FRONTEND=1
```
Then add the following to `client/docker/env/admin.env` and `client/docker/env/frontend.env`:
```
OPG_PHP_XDEBUG_ENABLED=true
OPG_PHP_XDEBUG_REMOTE_HOST=docker.for.mac.localhost
OPG_PHP_XDEBUG_REMOTE_PORT=9001
OPG_PHP_XDEBUG_IDEKEY=PHPSTORM
```
**Note** the above is an example for PHP Storm using a Mac. You will need to configure your IDE, ensuring that the same port is used in the IDE as that set above.

Now build the image and run the container. You can confirm installation by running `php -v` in the container and seeing that it reports the Xdebug version.

To install Xdebug on the API, set the flag to true in the `.env` file (see above), and add the same config as above to `api/docker/env/api.env`. **Note** that this impacts local performance dramatically and often times out when hitting the application through the frontend, so API debugging is best done in isolation by hitting endpoints via Postman, and uninstalling Xdebug when finished by setting the flag in `.env` to false

## PHPStorm PHPUnit XDebug setup

To enable step through xdebug in PHPStorm/IntelliJ you'll need to ensure the setup for PHP and PHPUnit are pointing to the relevant Docker PHP install.

### Create a Docker PHP CLI Interpreter
The steps below are for setting this up for the API container. This will need to be followed again for setting up Client/Admin with any api specific namings or file locations updated to match the client directory.

Go to Settings > Languages & Frameworks > PHP > Click `...` next to CLI Interpreter.

Add a new interpreter with `+` and select `From Docker, Vagrant, Vm, Remote...`. Choose `Docker Compose`, ensure the root docker-compose.yml file is selected for Configuration file. Give this a sensible name that includes `api`.

Back in Languages & Frameworks > PHP > Debug ensure the port in the Xdebug section matches the value of `OPG_PHP_XDEBUG_REMOTE_PORT` in `api/docker/env/api.env`.

In Languages & Frameworks > PHP > Test Frameworks add a new configuration type and select `PHP Unit by Remote Interpreter` and select the CLI Interpreter that was just created for Docker api. PHP Unit Library > select `Use Composer Autoloader` and enter `/var/www/vendor/autoload.php` in `Path to script`. Click the refresh symbol and confirm a version of PHP Unit is displayed here - if not then recheck the path mappings set up above. For `Default Configuration File` enter `/var/www/tests/phpunit/phpunit.xml` and  `Default Bootstrap File` enter `/var/www/tests/phpunit/bootstrap.php`.

This should be everything required to have debugging set up for test runs. To confirm, find a test and add a breakpoint in the test itself or a class that is exercised during the test then click the play symbol next to the test function and hit the debug icon. Once the container starts up you should drop into debug mode and have the ability to step through the code and execute commands against the current state of the app.
