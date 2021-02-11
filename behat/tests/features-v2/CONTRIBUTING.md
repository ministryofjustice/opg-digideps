The aim of this test suite is two fold: to give us confidence that all mission critical features are functional, and to serve as a high level documentation to understand the features of the application.

It is therefore most useful to keep this test suite targeted towards the high level features of the application, as opposed to low level intricacies that might be better suited to unit tests. All tests should fall into a high level feature of the applications, such as `registration`, `reporting`, and `report-review`.

If you are about to write a behat test which does not fall into any of the directories in this folder, discuss with other developers about creating a new directory, and perhaps consider using unit tests instead.

### Directory structure guidelines

Each `.feature` file should be apart of a high level feature, and should belong in the folder for that feature.

Where possible, avoid any duplication of tests. This means only writing deputy type specific tests if the feature behaves differently for each deputy type.

##### Testing for different deputy types

For example, suppose we add a new section to the report called `property`. If the section behaves the same for all deputy types, we would create the following inside the `reporting/sections`:

```$xslt
property/
  property.feature
```

If the section differs between lay and organisation based deputies (but not between PA and professional), create two test files, e.g:

```$xslt
property/
  property.lay.feature
  property.org.feature
```

If the section differs between lay, PA, and professional deputies, create three test files, e.g:

```$xslt
property/
  property.lay.feature
  property.pa.feature
  property.prof.feature
```

This three tiered approach ensures our tests are only as granular as they need to be.

##### Testing for different user types

If a high level feature involves functionality from the frontend _and_ the admin area, then create a `deputy` and `admin` directory within the feature directory.

Do not mix admin testing inside frontend testing.

### Test isolation

Between each scenario the database is truncated and an admin and super admin user are loaded as fixtures. This was a design choice to ensure tests can be run in isolation and are not reliant on the state of previous steps.

To ensure each feature serves as documentation that is easy to follow, and to decouple our tests, each feature file should be independent of all others and each scenario should be able to be run in isolation. Any fixture creation necessary for a fixture should take place within its file.
