# Xdebug

## Enabling Xdebug

### Client OR API

To use Xdebug in the `frontend` and/or `admin` app, it must be installed on the client image. To install, you will need to either manually pass `REQUIRE_XDEBUG_CLIENT=1` as an env var when bringing up the app or run the make command `up-app-xdebug-client`

To install Xdebug on the API, either manually pass `REQUIRE_XDEBUG_API=1` as an env var when bringing up the app or run the make command `up-app-xdebug-api`.

### Client AND API

It's possible to have Xdebug enabled in both client and api apps at the same time but this requires opening each app in separate instances of PHPStorm by either using `File -> Open` or, once the command line helper has been installed via `Tools -> Create command-line launcher`, via command line:

```bash
$ pstorm api client
```

Opening PHPStorm in api or client will create a fresh PHPStorm configuration separate to when you open the whole repository at root level. The new configuration will need to be set up slightly differently when compared to opening at the root of the project in order to enable Xdebug in both apps - see the `Edit Run/Debug configurations` section below for details.

The other sections below will need to be followed for each instance of PHPStorm open.

### Confirming Xdebug enabled

You can confirm installation by running `php -v` in the container and seeing that it reports the Xdebug version.

Since v3 performance with Xdebug enabled is only slightly slower than without so leaving Xdebug enabled shouldn't be a huge performance hit compared with previous versions.

## PHP Server setup

### Client OR API

Go to `Preferences` and select `Servers` under the `PHP` dropdown. When setting up each server (client and api) you will need to add and click the following:

* Add a `name` for each server and a `host name` - api (for api) and digideps.local (for client)
* Set `Port` to `443` (applies to both api and client)
* Select tick box `Use path mappings`
* Find root directory for the app (API or Client), click the pencil icon and set as `/var/www` then click apply

### Client AND API

Follow the instructions for `Client OR API` in each open instance of PHPStorm - setup client in the client instance and API in the API instance.

## Create a Docker PHP CLI Interpreter

### Client OR API

The steps below are for setting this up for the `API` container. This will need to be followed again for setting up `Client` with any api specific namings or file locations updated to match the client directory.

* Go to Preferences > PHP > Click PHP > `...` next to CLI Interpreter.

* Add a new interpreter with `+` and select `From Docker, Vagrant, Vm, Remote...`. Choose `Docker Compose`,ensure the root docker-compose.yml file is selected for Configuration file. Give this a sensible name that includes `api`.
* Add name and select service (client or api)

Complete the steps under `Further steps required for both setups`

### Client AND API

The steps below are for setting this up for the `API` container in an instance of PHPStorm that contains only the API directory. This will need to be followed again for setting up `Client` in an instance of PHPStorm that contains only the Client directory with any api specific namings or file locations updated to match the client directory.

Before starting this config ensure that the application is running in Docker.

* Go to Preferences > PHP > Click PHP > `...` next to CLI Interpreter.

* Add a new interpreter with `+` and select `From Docker, Vagrant, Vm, Remote...`. Choose `Docker`, choose `opg-digideps_api:latest` for `image name`. Give this a suitable name that includes `api`.

Complete the steps under `Further steps required for both setups`

### Further steps required for both setups

* Click on 'NEW' button next to Servers, make sure 'Docker for Mac' is selected and click 'ok'. Click apply, then click refresh icon and apply.

* In PHP > Debug ensure the port in the Xdebug section is set to `9003`. Un-tick the three boxes under 'Xdebug'

* In PHP > Test Frameworks add a new configuration type and select `PHP Unit by Remote Interpreter` and select the CLI Interpreter that was just created for Docker API.
* For PHP Unit Library > select `Use Composer Autoloader` and enter `/var/www/vendor/autoload.php` in `Path to script`.
* Click the refresh symbol and confirm a version of PHP Unit is displayed here - if not then recheck the path mappings set up above.
* For `Default Configuration File` enter `/var/www/tests/phpunit/phpunit.xml` and `Default Bootstrap File` enter `/var/www/tests/phpunit/bootstrap.php`.

* To set up the client container, you will need to enter the same file locations except for the `Default config file` which needs to be `/var/www/tests/phpunit.xml` and `Default Bootstrap File` should be set to `/var/www/tests/phpunit/bootstrap.php`.

## Edit Run/Debug configurations

### Frontend/Admin OR API

* In Run > Edit Configurations, press `+` and select `PHP Remote Debug`
* Add name (api or client) and select the tick box `Filter debug connection by IDE key` and enter `PHPSTORM` in the `IDE key` field.
* Click the `Server` dropdown and select the server that corresponds to the service.

### Frontend/Admin AND API

After following the instructions on `Enabling Xdebug - Frontend/Admin AND API` above set the following Debug configuration settings:

#### API

* In Run > Edit Configurations, press `+` and select `PHP Remote Debug`
* Set name as API and select the tick box `Filter debug connection by IDE key` and enter `PHPSTORM-API` in the `IDE key` field.
* Click the `Server` dropdown and select the `API` server created in the previous section.

#### Client

* In Run > Edit Configurations, press `+` and select `PHP Remote Debug`
* Set name as API and select the tick box `Filter debug connection by IDE key` and enter `PHPSTORM-CLIENT` in the `IDE key` field.
* Click the `Server` dropdown and select the `Client` server created in the previous section.

### Confirming setup

This should be everything required to have debugging set up for test runs or while using the app. To confirm, find a test and add a breakpoint in the test itself or a class that is exercised during the test then click the play symbol next to the test function and hit the debug icon. Once the container starts up you should drop into debug mode and have the ability to step through the code and execute commands against the current state of the app.
