@v2 @v2_reporting_1 @section-navigation
Feature: Section navigation - Lay combined
#
    @lay-combined-high-not-started
    Scenario: Actions
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the actions report section
        Then the previous section should be "Debts"
        And the next section should be "Any other information"

    @lay-combined-high-not-started
    Scenario: Any other information
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the any other information report section
        Then the previous section should be "Actions"
        And the next section should be "Documents"
#
    @lay-combined-high-not-started
    Scenario: Assets
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the assets report section
        Then the previous section should be "Money out"
        And the next section should be "Debts"

    @lay-combined-high-not-started
    Scenario: Bank Accounts
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the accounts report section
        Then the previous section should be "Client benefits check"
        And the next section should be "Deputy expenses"

    @lay-combined-high-not-started
    Scenario: Contacts
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the contacts report section
        Then the previous section should be "Decisions"
        And the next section should be "Visits and care"

    @lay-combined-high-not-started
    Scenario: Debts
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the debts report section
        Then the previous section should be "Assets"
        And the next section should be "Actions"

    @lay-combined-high-not-started
    Scenario: Decisions
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the decisions report section
        Then the previous section should be "Report overview"
        And the next section should be "Contacts"

    @lay-combined-high-not-started
    Scenario: Deputy Expenses
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the deputy expenses report section
        Then the previous section should be "Bank accounts"
        And the next section should be "Gifts"

    @lay-combined-high-not-started
    Scenario: Documents
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the documents report section
        Then the previous section should be "Any other information"
        And the next section should be "Report overview"

    @lay-combined-high-not-started
    Scenario: Gifts
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the gifts report section
        Then the previous section should be "Deputy expenses"
        And the next section should be "Money transfers"

    @lay-combined-high-not-started
    Scenario: Money In
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the money in report section
        Then the previous section should be "Money transfers"
        And the next section should be "Money out"

    @lay-combined-high-not-started
    Scenario: Money Out
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the money out report section
        Then the previous section should be "Money in"
        And the next section should be "Assets"

    @lay-combined-high-not-started
    Scenario: Money Transfers
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the money transfers report section
        Then the previous section should be "Gifts"
        And the next section should be "Money in"

    @lay-combined-high-not-started
    Scenario: Visits and Care
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the visits and care report section
        Then the previous section should be "Contacts"
        And the next section should be "Health and lifestyle"

    @lay-combined-high-not-started
    Scenario: Health and Lifestyle
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the health and lifestyle report section
        Then the previous section should be "Visits and care"
        And the next section should be "Client benefits check"
