@v2 @v2_reporting_2 @money-transfers
Feature: Money Transfers

    @lay-pfa-high-completed
    Scenario: A user attempts to add a money transfer when they only have one account type
        Given a Lay Deputy has a completed report
        And I view the report overview page
        And I visit the money transfers report section
        Then I should not be able to add a transfer due to having fewer than two accounts


    @lay-pfa-high-not-started
    Scenario: A user adds add a money transfer
        Given a Lay Deputy has not started a report
        When I view the report overview page
        And I visit the accounts report section
        And I add one of each account type with valid details
        And I follow link back to report overview page
        And I visit the money transfers report section
        And I confirm that I have a transfer to add
        Then I add the transfer details between two accounts
        Then I should see the transfer listed on the money transfers summary page


    @lay-pfa-high-not-started
    Scenario: A user deletes a money transfer that they had previously added
        Given a Lay Deputy has not started a report
        And I view the report overview page
        Then I visit the accounts report section
        And I add one of each account type with valid details
        And I follow link back to report overview page
        And I visit the money transfers report section
        And I confirm that I have a transfer to add
        Then I add the transfer details between two accounts
        And I should see the transfer listed on the money transfers summary page
        When I remove the money transfer I just added
        Then I should be on the money transfers starting page and see entry deleted


    @lay-pfa-high-not-started
    Scenario: A user adds add a valid description to their money transfer
        Given a Lay Deputy has not started a report
        When I view the report overview page
        And I visit the accounts report section
        And I add one of each account type with valid details
        And I follow link back to report overview page
        And I visit the money transfers report section
        And I confirm that I have a transfer to add
        Then I add the transfer details between two accounts with a description of 75 characters
        Then I should see the transfer listed on the money transfers summary page
