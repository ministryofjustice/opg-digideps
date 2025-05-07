@v2 @v2_reporting_1 @section-navigation
Feature: Section navigation from summary pages - Ndr

    @ndr-completed
    Scenario: Visits and Care
        Given a Lay Deputy has a completed NDR report
        And I visit the report overview page
        When I visit the visits and care summary report section
        Then the previous section should be "Report overview"
        And the next section should be "Deputy expenses"

    @ndr-completed
    Scenario: Deputy expenses
        Given a Lay Deputy has a completed NDR report
        And I visit the report overview page
        When I visit the deputy expenses summary report section
        Then the previous section should be "Visits and care"
        And the next section should be "Client benefits check"

    @ndr-completed
    Scenario: Clients benefits check
        Given a Lay Deputy has a completed NDR report
        And I visit the report overview page
        When I visit the client benefits check summary page
        Then the previous section should be "Deputy expenses"
        And the next section should be "Income and benefits"

    @ndr-completed
    Scenario: Income and benefits
        Given a Lay Deputy has a completed NDR report
        And I visit the report overview page
        When I visit the income benefits summary section
        Then the previous section should be "Client benefits check"
        And the next section should be "Bank accounts"

    @ndr-completed
    Scenario: Accounts
        Given a Lay Deputy has a completed NDR report
        And I visit the report overview page
        When I visit the accounts summary section
        Then the previous section should be "Income and benefits"
        And the next section should be "Assets"

    @ndr-completed
    Scenario: Assets
        Given a Lay Deputy has a completed NDR report
        And I visit the report overview page
        When I visit the assets summary report section
        Then the previous section should be "Bank accounts"
        And the next section should be "Debts"

    @ndr-completed
    Scenario: Debts
        Given a Lay Deputy has a completed NDR report
        And I visit the report overview page
        When I visit the debts summary report section
        Then the previous section should be "Assets"
        And the next section should be "Actions"

    @ndr-completed
    Scenario: Actions
        Given a Lay Deputy has a completed NDR report
        And I visit the report overview page
        When I visit the actions summary report section
        Then the previous section should be "Debts"
        And the next section should be "Any other information"

    @ndr-completed
    Scenario: Any other information
        Given a Lay Deputy has a completed NDR report
        And I visit the report overview page
        When I visit the any other information summary report section
        Then the previous section should be "Actions"
        And the next section should be "Report overview"
