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

Setup local environment following instructions on the `opg-digi-deps-docker` repository.

`app/config/parameters.yml` is generated via docker init scripts.

## Architectural notes

### Testing
see [here](tests/README.md)

## Frontend technical notes

### Gulp
The frontend components rely on Gulp to be built and assembled. The main tasks involved in this part of the build are copying image assets, compiling SASS to CSS and concatinating JS into a single file and then running uglify to minify it.

The Gulp build file has many targets, but the 3 that are of most interest are **default**, **watch** and **development**.

If you use Docker whilst developing the frontend, the best way to work with these assets is to connect to the docker frontend container:

    docker exec -it opgdigidepsdocker_frontend_1 bash

Once in the container you can simply enter:

    gulp watch

This will compile all the assets, in development (debug) mode, and will then continue to watch for changes to those files until you stop it with CTRL+C.

Each of the steps in Gulp are documented in the Gulpfile.

### Browser Testing

There are notes in the readme file in the test folder to describe the best way to run regular tests and how to attempt to run those same tests with a real browser via browserstack.

With special thanks to [BrowserStack](https://www.browserstack.com) for providing cross browser testing.


### Dependencies

A brief note about dependencies. First, although we use node 4.x when building containers we also specify NPM version 3. This version of NPM has a number of important improvements over NPM version 2 which is bundled with node 4, the main one being directory structure.

Dependencies are versioned to avoid accidently breaking the build. From time to time new review those dependencies to see if a valid new version is available, the chief of these should be [govuk-elements-sass](https://www.npmjs.com/package/govuk-elements-sass)


## Coding standards

[PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/)

Run `php-cs-fixer fix` to format files

## License

The OPG Digideps Client is released under the MIT license, a copy of which can be found in [LICENSE](LICENSE).
