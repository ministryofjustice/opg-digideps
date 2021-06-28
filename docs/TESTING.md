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

##Emails in non-production environments

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
