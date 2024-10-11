@v2 @v2_reporting_2 @section-navigation
Feature: Section navigation from summary pages - Lay

    @lay-pfa-high-completed
    Scenario: Decisions
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the decisions summary report section
        Then the previous section should be "Report overview"
        And the next section should be "Contacts"
        And the link to the report overview page should display the correct reporting years

    @lay-pfa-high-completed
    Scenario: Contacts
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the contacts summary report section
        Then the previous section should be "Decisions"
        And the next section should be "Visits and care"
        And the link to the report overview page should display the correct reporting years

    @lay-pfa-high-completed
    Scenario: Visits and Care
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the visits and care summary report section
        Then the previous section should be "Contacts"
        And the next section should be "Client benefits check"
        And the link to the report overview page should display the correct reporting years

    @lay-pfa-high-completed
    Scenario: Client benefits check
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the client benefits check summary page
        Then the previous section should be "Visits and care"
        And the next section should be "Accounts"
        And the link to the report overview page should display the correct reporting years

    @lay-pfa-high-completed
    Scenario: Accounts
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        Then I should see "Client details"
        When I visit the accounts summary section
        Then the previous section should be "Client benefits check"
        And the next section should be "Deputy expenses"
        And the link to the report overview page should display the correct reporting years

    @lay-pfa-high-completed
    Scenario: Deputy Expenses
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the deputy expenses summary report section
        Then the previous section should be "Bank accounts"
        And the next section should be "Gifts"
        And the link to the report overview page should display the correct reporting years

    @lay-pfa-high-completed
    Scenario: Gifts
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the gifts summary report section
        Then the previous section should be "Deputy expenses"
        And the next section should be "Money transfers"
        And the link to the report overview page should display the correct reporting years

    @lay-pfa-high-completed
    Scenario: Money In
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the money in summary report section
        Then the previous section should be "Money transfers"
        And the next section should be "Money out"
        And the link to the report overview page should display the correct reporting years

    @lay-pfa-high-completed
    Scenario: Money Out
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the money out summary section
        Then the previous section should be "Money in"
        And the next section should be "Assets"
        And the link to the report overview page should display the correct reporting years

    @lay-pfa-high-completed
    Scenario: Assets
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the assets summary report section
        Then the previous section should be "Money out"
        And the next section should be "Debts"
        And the link to the report overview page should display the correct reporting years

    @lay-pfa-high-completed
    Scenario: Debts
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the debts summary report section
        Then the previous section should be "Assets"
        And the next section should be "Actions"
        And the link to the report overview page should display the correct reporting years

    @lay-pfa-high-completed
    Scenario: Actions
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the actions summary report section
        Then the previous section should be "Debts"
        And the next section should be "Any other information"
        And the link to the report overview page should display the correct reporting years


    @lay-pfa-high-completed
    Scenario: Any other information
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the any other information summary report section
        Then the previous section should be "Actions"
        And the next section should be "Documents"
        And the link to the report overview page should display the correct reporting years

    @lay-pfa-high-completed
    Scenario: Documents
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I visit the documents summary report section
        Then the previous section should be "Any other information"
        And the next section should be "Report overview"
        And the link to the report overview page should display the correct reporting years
