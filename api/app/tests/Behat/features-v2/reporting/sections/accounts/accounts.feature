@v2 @v2_reporting_1 @accounts
Feature: Accounts (Lay / PA / Prof share same functionality)

    @lay-pfa-high-not-started
    Scenario: A user adds one of each account type
        Given a Lay Deputy has not started a report
        And I view the report overview page
        Then I should see "bank-accounts" as "1 account"
        And I visit the accounts report section
        And I add one of each account type with valid details
        Then I should see the expected accounts on the summary page
        When I follow link back to report overview page
        Then I should see "bank-accounts" as "8 accounts"

    @lay-pfa-high-not-started
    Scenario: A user incorrectly enters an account before correctly entering it
        Given a Lay Deputy has not started a report
        When I go to add a new current account
        And I miss one of the fields
        Then I get the correct validation warnings
        When I try to enter letters where it should be digits
        Then I get the correct validation warnings
        When I correctly enter account details
        Then I should see the expected accounts on the summary page

    @lay-pfa-high-completed
    Scenario: A user edits an existing account
        Given a Lay Deputy has a completed report
        And I visit the accounts report section
        Then I should be on the accounts summary page
        When I update my current account to a different one
        Then I should see the expected accounts on the summary page

    @lay-pfa-high-not-started
    Scenario: A user adds accounts and then changes their mind and deletes them
        Given a Lay Deputy has not started a report
        And I visit the accounts report section
        And I add a couple of new accounts
        Then I should see the expected accounts on the summary page
        When I remove the second account
        Then I should see the expected accounts on the summary page
        When I remove the remaining account
        Then I should be on the accounts summary page
        When I follow link back to report overview page
        Then I should see "bank-accounts" as "1 account"

  @lay-pfa-high-not-started
  Scenario: A user adds an account with zero balance and sees the "is account closed?" question
    Given a Lay Deputy has not started a report
    And I visit the accounts report section
    And I add an account with a zero balance
    When I click save and continue
    Then I should be prompted to select an answer to the account closed question
    When I select "Yes" for the account closed question
    Then I should see the account on the summary page marked as closed
