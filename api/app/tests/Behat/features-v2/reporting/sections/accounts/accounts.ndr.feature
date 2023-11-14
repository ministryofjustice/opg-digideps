@v2 @v2_reporting_1 @accounts.ndr
Feature: Accounts (NDR)

    @ndr-not-started
    Scenario: A user adds one of each account type
        Given a Lay Deputy has not started an NDR report
        And I view the NDR overview page
        Then I should see "bank-accounts" as "1 account"
        And I visit the accounts report section
        And I add one of each account type with valid details
        Then I should see the expected accounts on the summary page
        When I follow link back to report overview page
        Then I should see "bank-accounts" as "8 accounts"

    @ndr-not-started
    Scenario: A user incorrectly enters an account before correctly entering it
        Given a Lay Deputy has not started an NDR report
        When I go to add a new current account
        And I miss one of the fields
        Then I get the correct validation warnings
        When I try to enter letters where it should be digits
        Then I get the correct validation warnings
        When I correctly enter account details
        Then I should see the expected accounts on the summary page

    @ndr-completed
    Scenario: A user edits an existing account
        Given a Lay Deputy has a completed NDR report
        And I visit the accounts report section
        Then I should be on the accounts summary page
        When I update my current account to a different one
        Then I should see the expected accounts on the summary page

    @ndr-not-started
    Scenario: A user adds accounts and then changes their mind and deletes them
        Given a Lay Deputy has not started an NDR report
        And I visit the accounts report section
        And I add a couple of new accounts
        Then I should see the expected accounts on the summary page
        When I remove the second account
        Then I should see the expected accounts on the summary page
        When I remove the remaining account
        Then I should be on the accounts summary page
        When I follow link back to report overview page
        Then I should see "bank-accounts" as "1 account"
