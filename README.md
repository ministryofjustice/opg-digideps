# Complete the deputy report (Client)

## Overview

This app is the client used by deputy to submit their report to OPG.

Complete the deputy report is composed by
 - [Client](https://github.com/ministryofjustice/opg-digi-deps-client)
 - [API](https://github.com/ministryofjustice/opg-digi-deps-api)
 - [Docker config (private)](https://github.com/ministryofjustice/opg-digi-deps-docker)


## Frameworks and languages

- PHP 5.6
- Symfony 2.8
- Behat 3
- PHPUnit 4
- Twig
- Connects to API for data operations
- Uses [GOV.UK Frontend Toolkit](https://github.com/alphagov/govuk_frontend_toolkit)
- Uses [GOV.UK Template](https://github.com/alphagov/govuk_template)
- Uses [GOV.UK Elements](https://github.com/alphagov/govuk_elements)

## Setup

See the [Docker config](https://github.com/ministryofjustice/opg-digi-deps-docker) repository for instructions on how to set up the API and client containers locally.

## Testing

See [here](tests/README.md)

## Frontend technical notes

### Gulp

The frontend components rely on Gulp to be built and assembled. The main tasks involved in this part of the build are copying image assets, compiling SASS to CSS and concatinating JS into a single file and then running uglify to minify it.

Assets are automatically rebuilt with Gulp when you build the frontend image. To do so, call `docker-compose up -d --build` in the docker repo folder.

You can also run one of commands against the NPM image. For example, to lint all files:

```sh
docker-compose run --rm npm run lint
```

### Browser Testing

There are notes in the readme file in the test folder to describe the best way to run regular tests and how to attempt to run those same tests with a real browser via browserstack.

With special thanks to [BrowserStack](https://www.browserstack.com) for providing cross browser testing.

### Dependencies

Dependencies are versioned to avoid accidently breaking the build. From time to time new review those dependencies to see if a valid new version is available, the chief of these should be [govuk-elements-sass](https://www.npmjs.com/package/govuk-elements-sass).

## Coding standards

[PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/)

## Xdebug

Xdebug is installed on the container when your local `.env` file in the `opg-digi-deps-docker` repo contains `REQUIRE_XDEBUG_FRONTEND=true`. (See the `opg-digi-deps-docker` README for more information).

Once installed, you can set xdebug config values from `admin.env` and `frontend.env`. For the values to take effect, the env file must contain `OPG_PHP_XDEBUG_ENABLED=true`. The default values currently set are those required to step through the PHPSTORM IDE on a Mac. You will need to configure your IDE, ensuring that the same port is used in the IDE as that set in the `admin.env` and `frontend.env` files. 

## License

The OPG Digideps Client is released under the MIT license, a copy of which can be found in [LICENSE](LICENSE).
