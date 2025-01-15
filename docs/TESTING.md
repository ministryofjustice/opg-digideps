# Testing

This application uses two main testing technologies:

- PHPUnit performs unit tests for individual classes
- Behat performs user tests to ensure whole application journeys work

## How to run the tests

In order to run api tests locally, create a .env file in the root of the repo and add the test key found in AWS secrets
manager under the Digideps developer account, e.g.

```shell script
# .env

NOTIFY_API_KEY=fakeKeyGetRealValueFromAWS-123abcabc-abc1-12bn-65pp-12344567abc12-8j11j8d-4532-856s-7d55
```

### Unit tests using a docker test container

Frontend and Admin:

```shell script
$ make client-unit-tests
```

Api:

```shell script
$ make api-unit-tests
```

### Client unit tests using CLI

To run these tests without a docker container (useful for running tests quickly during dev as it avoids having to
keep rebuilding and restarting containers), ensure you have PHP 8.1 installed:

```shell script
$ php -version
PHP 8.1.31 (cli) (built: Nov 19 2024 15:24:51) (NTS)
```

You also need ImageMagick, which can be installed on a Mac using homebrew:

```shell script
$ brew install ImageMagick
```

Then do:

```shell script
$ docker compose -f docker-compose.yml -f docker-compose.unit-tests-client.yml up -d pact-mock

# ... wait for it to start ...

$ PACT_MOCK_SERVER_HOST=localhost PACT_MOCK_SERVER_PORT=1234 APP_ENV=dev APP_DEBUG=0 \
  AWS_ACCESS_KEY_ID=aFakeSecretAccessKeyId AWS_SECRET_ACCESS_KEY=aFakeSecretAccessKey \
  AWS_SESSION_TOKEN=fakeValue vendor/bin/phpunit -c tests/phpunit/phpunit.xml
```

To re-run the tests, you just need to run the second command again, unless you are changing mocks. If mocks change,
you'll need to restart the pact-mock server.

### Integration tests

Run all behat tests:

```shell script
$ make integration-tests
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

Behat tests are run from the api container against the frontend and creates fixture data in the environment's DB. This makes test failures easy to debug, but means that using the application during tests can cause failures.

### Suites

Our modern behat suites are designed to test one piece of application functionality in isolation. This means multiple suites could be run in parallel, since they use different data and don't depend on each other.

When creating new tests, try and make them so each suite is independent of all other suites and so that any data created does not impact other suites. This is usually a case of using a new report and users for each suite but ocassionally where we are looking at report counts or user counts, this can be more complex.

If possible, give your new tests one of the following tags:

- @v2_reporting_1
- @v2_reporting_2
- @v2_admin
- @v2_sequential_1
- @v2_sequential_2
- @v2_sequential_3

Look at the current times for the tags in github actions and add the tag for the one that applies to the logic of your new tests and has the lowest current running time. If your tests don't need to be run sequentially then add them to one of the non sequential runners.

### Fixture data

To support running tests in isolation, and parallelisation, any data required for a test should be created at the beginning of each test. To streamline this process you can tag tests with report or user types to automate the fixture creation:

| Tag | Data created |
|---- |----|
| `@lay-pfa-high-*` | Lay Deputy User, Client associated with User and a Property & Finances - High Assets report (102) |
| `@lay-pfa-low-*` | Lay Deputy User, Client associated with User and a Property & Finances - Low Assets report (103) |
| `@lay-health-welfare-*` | Lay Deputy User, Client associated with User and a Health & Welfare report (104) |
| `@ndr-*` | Lay Deputy User, Client associated with User and a New Deputy Report - Health & Welfare (104) |
| `@prof-admin-*` | Professional Admin User, Client associated with User, Organisation, Named Deputy and a Property & Finances - High Assets report (102) |
| `@admin` | Admin User |
| `@admin-manager` | Admin Manager User |
| `@super-admin` | Super Admin User |

Any tag above that ends with a `*` needs to have one of `not-started`, `completed` or `submitted` appended. This will determine the status of the report and if the report sections are completed:

| Tag | Data created |
|---- |----|
| `*-not-started` | A new report |
| `*-completed` | A completed report with dummy data added to required sections |
| `*-submitted` | A completed and submitted report and a report submission |

### Notable helper functions

A common pattern in our application is completing a section of the report by using a form and then having the responses summarised on one page. Summary pages are not uniform in design and use a number of different HTML elements to display data and any monetary values entered are summed up with totals displayed (in some instances subtotals are displayed as well). To try to streamline and simplify how we assert on the summary page there are some wrappers around the standard behat form filling functions that track form values ready to be asserted on summary pages. Form values are stored in associative arrays with the field name as a key and the entered value as the array value (e.g. '["yes_no[paidForAnything]"]' => "yes" ). [FormFillingTrait](../api/tests/Behat/bootstrap/v2/Common/FormFillingTrait.php) contains the functions, and the array used to store responses.

To use this system make sure you use the following functions when filling in forms:

Text fields:
- `fillInField()` - tracks text input
- `fillInFieldTrackTotal()` - tracks text input that relates to a cumulative value that is summed on the summary page
- `fillInDateFields()` - tracks and then converts numerical values to the apps standard date format (e.g. 1, 1, 2020 becomes 1 January 2020 when asserting on summary page)

Select fields:
- `chooseOption()` - tracks selected option and converts to a partial or full text representation if provided as an optional argument ready to assert on summary pages

Removing from summary pages
- `removeAnswerFromSection()` - removes a response via the summary page (and updates any associated tracked monetary values)

Editing from summary pages
- `editSelectAnswerInSection()` - edits an answer submitted in a select via the summary page
- `editFieldAnswerInSection()` - edits an answer submitted in a field via the summary page
- `editFieldAnswerInSectionTrackTotal()`  - edits an answer via the summary page (and updates any associated tracked monetary values)

#### Form Sections
As a common pattern in our domain is related to a form having sections, we have adopted this terminology in the helper functions to give some meaningful structure to responses. For example, when completing a multi-page form that deals with visits and care you could name each 'section' after the type of question being asked. By splitting up responses in to sections it gives greater freedom to assert on specific sections being completed (or not, if required), but if the logic is fairly simple you can assign a single section name when filling in fields, and the responses will be under one section name - see below for how this looks in practice:

```
array(2) {
      │   ["anyExpensesClaimed"]=>
      │   array(1) {
      │     [0]=>
      │     array(1) {
      │       ["yes_no[paidForAnything]"]=>
      │       string(3) "yes"
      │     }
      │   }
      │   ["expenseDetails"]=>
      │   array(2) {
      │     [0]=>
      │     array(2) {
      │       ["expenses_single[explanation]"]=>
      │       string(94) "Fugit sit nemo sit quia aspernatur eligendi soluta cumque perferendis deserunt incidunt autem."
      │       ["expenses_single[amount]"]=>
      │       int(789)
      │     }
      │     [1]=>
      │     array(2) {
      │       ["expenses_single[explanation]"]=>
      │       string(90) "Earum possimus qui ut inventore tempora ratione voluptatem aut perferendis illo vitae eum."
      │       ["expenses_single[amount]"]=>
      │       int(782)
      │     }
      │   }
      │ }
```

#### Form Section Answer Groups and Removing Responses
Some parts of our forms have values that are directly related to each other. In the above example this can be seen under the `expenseDetails` section. When testing remove functionality we would always want to remove both items, so they are grouped together and referred to in code as 'answer groups'. This allows the entries to be easily removed when required to ensure we don't try to assert on them on summary pages.

To remove an item use the `removeAnswerFromSection()` function providing a single field name contained in answer group to be removed, and they will both be removed.

#### Parallelisation
There are two levels of parallelisation involved with the behat tests. Firstly we have the processor level and then the container level.
Each container is a high specced container that has 4 processors (this is the maximum). We use `liuggio/fastest/` to parallelise our tests across processors.

The next level is at the container level. As we can get further speed improvements, we split out similar themed tests across containers with tags.

There is a built-in timer that ensures a tagged group of tests does not exceed 300 seconds total. If they do then you should split them into a new container.

To do this, we have added a override flag to the go runner task. You can add the tag to each feature file and use the override command in the circleci config
to spin up a new parallel task.

##Emails in non-production environments

Non-production environments don't send emails to avoid data leakage, confusion and embarrassment. This is achieved with a GOV.UK Notify "test" key, which causes Notify to behave as usual but not send the email out. Test emails can then be inspected through Notify's [admin interface][govuk-notify].

## Database Sync

The sync process between production and preproduction is handled as part of the pipeline using AWS tasks. To test locally run the sync service with the following commands:

```sh
docker compose run --rm sync ./backup.sh
docker compose run --rm sync ./restore.sh
```

## PHPStan

[PHPStan][phpstan] analyses and lints our PHP files to identify common issues which miight otherwise be missed, such as incorrect annotations or using undefined variables. It is run as part of CI against any files which were changed on the branch.

You can also run PHPStan manually. Note that you need to run it against each container separately, and can specify which paths (in this example "src" and "tests" to analyse).

```sh
docker compose run --rm api bin/phpstan analyse src tests --memory-limit=0 --level=max
docker compose run --rm frontend bin/phpstan analyse src tests --memory-limit=0 --level=max
```

[mockery]: http://docs.mockery.io/en/latest/
[prophecy]: https://github.com/phpspec/prophecy
[phpstan]: https://github.com/phpstan/phpstan
[govuk-notify]: https://www.notifications.service.gov.uk/

## Running your tests from PHPStorm

To set up PHP storm to run your tests, you should use the test suites on the docker containers.

It's quickest if you already have an environment up and just run them on existing containers.

To set this up in PHPStorm, you should perform the following steps:

1)
   - Open PhpStorm->Settings... and click on PHP tab on left hand side.
   - Check php language level is set to current php language level (currently 8.1)
   - Find Cli interpreter box and click on the `...`
   - Click on `+` to add a new interpreter and call it frontend-app
     - Pick from Docker,Vagrant…
     - Docker Compose as the type
     - Create a new server and call it docker and pick docker for mac (other options can remain untouched). You will use this for both interpreters.
     - Configuration files should be ./docker-compose.yml; ./docker-compose.override.yml
     - Service should be frontend-app (should now be selectable in drop down as it recognises it from docker compose file) if setting up client tests or api-app if setting up api tests.
     - Environment variables can be set to some defaults to avoid seeing warnings: `REQUIRE_XDEBUG_CLIENT=False;XDEBUG_IDEKEY_CLIENT=FOO;XDEBUG_IDEKEY_API=FOO;REQUIRE_XDEBUG_API=False`
     - PHP interpreter path php
     - Once created there will be a few more options. Choose Lifecycle->Connect to existing container.
     - Under general check that php executable is set to php and a PHP version shows below with a little blue i symbol. This means it recognises your php executable.
   - Repeat all steps in process 1 but for api-app instead of frontend-app
2)
   - Go to PHPStorm->Settings… and the choose PHP->Test Frameworks
   - Click the `+` to add a new test framework
     - Pick PHPUnit by remote interpreter and choose frontend-app (or api-app if you’re creating api-app framework) which you created in step 1
     - Cli interpreter and Path Mappings should be correct
     - Under PHPUnit Library, pick Use Composer Autoloader
     - For path to script enter `/var/www/vendor/autoload.php` and hit the little refresh icon next to the field
     - Under Test Runner, tick both default configuration file and default bootstrap file. The locations for these are different on frontend and api. On frontend it’s `/var/www/tests/phpunit/phpunit.xml` and `/var/www/tests/phpunit/bootstrap.php` whereas on api side it’s `/var/www/tests/Unit/phpunit.xml` and `/var/www/tests/Unit/bootstrap.php`
3)
   - That's all there is to it. Now you can switch between contexts for running your tests by going to PHPStorm->Settings…->PHP and changing the interpreter
   - When you have the correct interpreter for your tests, you can click on the green arrow icon by individual tests to run them individually

## Smoke tests
Smoke tests run as part of our pipeline to check that their aren't any environmental issues outside
of the scope of our unit and integration tests in production.

Please read this [confluence documentation](https://opgtransform.atlassian.net/wiki/spaces/DigiDeps/pages/3778215945/Smoke+Tests) for more information on this.
