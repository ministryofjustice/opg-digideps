Feature: deputy / report / edit accounts

    @deputy
    Scenario: Setup the test user
      Given I load the application status from "report-submit-pre"
#      And I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
#      Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
#      When I create a new "Lay Deputy" user "Account" "Smith" with email "behat-account@publicguardian.gsi.gov.uk"
#      And I activate the user with password "Abcd1234"
#      #Then I should be on "user/details"
#      And I set the user details to:
#          | name | John | Doe |
#          | address | 102 Petty France | MOJ | London | SW1H 9AJ | GB |
#          | phone | 020 3334 3555  | 020 1234 5678  |
#      When I set the client details to:
#            | name | Peter | White |
#            | caseNumber | 123456ABC |
#            | courtDate | 1 | 1 | 2014 |
#            | allowedCourtOrderTypes_0 | 2 |
#            | address |  1 South Parade | First Floor  | Nottingham  | NG1 2HT  | GB |
#            | phone | 0123456789  |
#      And I set the report end date to "1/1/2015"
#      Then the URL should match "report/\d+/overview"
#      Then I am on "/logout"
#      And I reset the email log
#      Then I save the application status into "accountuser"
#
#    @deputy
#    Scenario: change opening balance explanation
#        When I load the application status from "accountuser"
#        And I am logged in as "behat-account@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        Then I follow "edit-accounts"
#        # Create an account with an opening balance explanation
#        And I fill in the following:
#            | account_bank    | HSBC - main account |
#            | account_accountNumber | 9911 |
#            | account_sortCode_sort_code_part_1 | 22 |
#            | account_sortCode_sort_code_part_2 | 22 |
#            | account_sortCode_sort_code_part_3 | 22 |
#            | account_openingDate_day   | 1 |
#            | account_openingDate_month | 2 |
#            | account_openingDate_year  | 2014 |
#            | account_openingBalance  | 100.00 |
#            | account_openingDateExplanation | Test One |
#        And I press "account_save"
#        And the form should be valid
#        # Goto the account and edit it to see the account opening explanation
#        And I click on "account-9911"
#        Then I click on "edit-account-details"
#        And the "account_openingDateExplanation" field should contain "Test One"
#        # Now fill in the account transactions and closing balance.
#        Then I fill in the following:
#            | transactions_moneyIn_0_amount       | 1 |
#            | transactions_moneyOut_0_amount      | 1 |
#        And I press "transactions_saveMoneyOut"
#        Then I fill in the following:
#            | accountBalance_closingDate_day   | 1 |
#            | accountBalance_closingDate_month | 1 |
#            | accountBalance_closingDate_year  | 2015 |
#            | accountBalance_closingBalance    | 100.00 |
#        And I press "accountBalance_save"
#        And I click on "edit-account-details"
#        Then the "account_openingDateExplanation" field should contain "Test One"
#        And I fill in the following:
#            | account_openingDateExplanation | Test Two |
#        Then I press "account_save"
#        And I click on "edit-account-details"
#        Then the "account_openingDateExplanation" field should contain "Test Two"
