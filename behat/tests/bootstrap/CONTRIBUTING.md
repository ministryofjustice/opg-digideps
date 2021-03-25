## Step definitions and feature contexts
Whenever possible we should rely on the standard step definitions provided by behat, mink or extensions such as behatch.

All new step definitions should be created in `bootstrap/v2` separated into folders linked to the theme of the tests or features they are used with:

```php
bootstrap/
    v2/
        /reporting
            sections/
                ReportingSectionsFeatureContext.php
                ContactsSectionTrait.php
                ...
```

Any code that is specifically linked to a feature should be in its own trait file and `use`ed in a feature context file. Traits are used as a way to keep code focused on a single responsibility, but it also means we can include a single feature context file in behat.yml suite config to avoid duplication definition errors that occur when adding multiple contexts that extend from a base context:

```yaml
suites:
        contacts:
            description: Covering the 'Contacts' section of the report
            paths: [ "%paths.base%/features-v2/reporting/sections/contacts" ]
            contexts:
                - DigidepsBehat\v2\Reporting\Sections\ReportingSectionsFeatureContext
```

Always extend from `BaseFeatureContext.php` when creating new feature context files as it contains functions that are commonly used across many features and allows us to keep tests state and fixture free by creating data on demand while keeping it accessible throughout the whole feature test. For example, rather than relying on a fixture file we can dynamically create uniquely named users and other objects that are accessible within `BaseFeatureContext` to avoid data collision and allow for parallel test runs.

If the step definition we need to write involves a number of complex actions we should look to break this out into focused functions with a view to enable re-use and only expose the step definitions we absolutely need. This will ensure there aren't duplicated step definitions and keep surprises when working with the interface to a minimum.

Steps defined in a trait or feature context file that later required in unrelated feature tests should be extracted and moved to `/common` in either `BaseFeatureContext.php or` a trait that is included in BaseFeatureContext.
