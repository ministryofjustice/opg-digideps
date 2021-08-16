@v2 @section-navigation
Feature: Section navigation - Public Authority

    @lay-pfa-high-not-started
    Scenario: Accounts
        Given a Lay Deputy has not started a report
        When I visit the accounts report section
        Then the previous section should be "xxx"
        And the next section should be "xxx"
