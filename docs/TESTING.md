# Testing

This application uses two main testing technologies:

- PHPUnit performs unit tests for individual classes
- Behat performs user tests to ensure whole application journeys work

## How to run the tests

In order to run tests locally, create a .env file in the root of the repo and add the test key found in AWS secrets manager under the Digideps developer account:

e.g.
```shell script
# .env

NOTIFY_API_KEY=fakeKeyGetRealValueFromAWS-123abcabc-abc1-12bn-65pp-12344567abc12-8j11j8d-4532-856s-7d55
```

### Unit tests

Frontend and Admin:

```shell script
$ make client-unit-tests
```

Api:

```shell script
$ make api-unit-tests
```

### Integration tests

Run all behat tests:

```shell script
$ make behat-tests
```

Run a specific suite:

```shell script
$ make behat-suite suite=<NAME OF SUITE>
```
Run a specific profile and suite:

```shell script
$ make behat-profile-suite profile=<NAME OF PROFILE> suite=<NAME OF SUITE>
```

## PHPUnit

The PHPUnit tests are stored in a directory structure matching `src/App`.

We use [Prophecy][prophecy] (and in some cases [Mockery][mockery]) to mock classes and entities which are not being tested. Client unit tests of controllers should extend `AbstractControllerTestCase` and can mock Symfony containers using the `injectProphecyService` method.

## Behat

Behat tests are run against the environment's client container, meaning test data is stored in the corresponding database. This makes test failures easy to debug, but means that using the application during tests can cause failures.

### Suites

Our modern behat suites are designed to test one piece of application functionality in isolation. This means multiple suites could be run in parallel, since they use different data and don't depend on each other.

There are however 6 older suites which are much larger and have a lot of complicated dependent data. These cannot easily be broken down further, but are slowly being replaced with smaller suites to eventually be phased out.

See behat/tests/behat.yml for suite descriptions.

## Emails in non-production environments

Non-production environments don't send emails to avoid data leakage, confusion and embarrassment. This is achieved with a GOV.UK Notify "test" key, which causes Notify to behave as usual but not send the email out. Test emails can then be inspected through Notify's [admin interface][govuk-notify].

## Database Sync

The sync process between production and preproduction is handled as part of the pipeline using AWS tasks. To test locally run the sync service with the following commands:

```sh
docker-compose run --rm sync ./backup.sh
docker-compose run --rm sync ./restore.sh
```

## PHPStan

[PHPStan][phpstan] analyses and lints our PHP files to identify common issues which miight otherwise be missed, such as incorrect annotations or using undefined variables. It is run as part of CI against any files which were changed on the branch.

You can also run PHPStan manually. Note that you need to run it against each container separately, and can specify which paths (in this example "src" and "tests" to analyse).

```sh
docker-compose run --rm api bin/phpstan analyse src tests --memory-limit=0 --level=max
docker-compose run --rm frontend bin/phpstan analyse src tests --memory-limit=0 --level=max
```

[mockery]: http://docs.mockery.io/en/latest/
[prophecy]: https://github.com/phpspec/prophecy
[phpstan]: https://github.com/phpstan/phpstan
[govuk-notify]: https://www.notifications.service.gov.uk/
