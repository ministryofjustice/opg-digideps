@v2 @v2_reporting_2 @income-benefits
Feature: Income Benefits (NDR)

    @ndr-not-started
    Scenario: A user has no other incomes or benefits
        Given a Lay Deputy has not started an NDR report
        And I visit the report overview page
        Then I should see "income-benefits" as "not started"
        When I view and start the income benefits report section
        And I have no other income or benefits
        Then I should see the expected income benefits section summary
        When I follow link back to report overview page
        Then I should see "income-benefits" as "finished"

    @ndr-not-started
    Scenario: A user has state benefits, no other regular income or compensation and a one-off payment
        Given a Lay Deputy has not started an NDR report
        When I view and start the income benefits report section
        And I have 2 items from the state benefits list
        And I receive a state pension
        But I don't receive any other regular income
        And I don't have any compensation awards or damages
        But I have a one-off payment
        Then I should see the expected income benefits section summary

    @ndr-completed
    Scenario: A user edits the income benefits to say they receive a state pension and other regular income
        Given a Lay Deputy has a completed NDR report
        And I visit the report overview page
        Then I should see "income-benefits" as "finished"
        When I goto the income and benefits summary page
        And I edit state pension to say yes
        Then I should see the expected income benefits section summary
