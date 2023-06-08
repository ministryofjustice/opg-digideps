@v2 @v2_reporting_2 @money-transfers
Feature: Money Transfers

    @lay-pfa-high-completed
    Scenario: A user attempts to add a money transfer when they only have one account type
        Given a Lay Deputy has a completed report
        And I view the report overview page
        And I visit the money transfers report section
        Then I should not be able to add a transfer due to having fewer than two accounts
