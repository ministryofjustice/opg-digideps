Feature: Accounts

    @accounts @deputy
    Scenario: change opening balance explanation
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I follow "tab-accounts"
        # Create an account with an opening balance explanation
        And I fill in the following:
            | account_bank    | HSBC - main account | 
            | account_accountNumber_part_1 | 9 | 
            | account_accountNumber_part_2 | 9 | 
            | account_accountNumber_part_3 | 1 | 
            | account_accountNumber_part_4 | 1 | 
            | account_sortCode_sort_code_part_1 | 22 |
            | account_sortCode_sort_code_part_2 | 22 |
            | account_sortCode_sort_code_part_3 | 22 |
            | account_openingDate_day   | 1 |
            | account_openingDate_month | 2 |
            | account_openingDate_year  | 2014 |
            | account_openingBalance  | 100.00 |
            | account_openingDateExplanation | Test One |
        And I press "account_save"
        And the form should be valid
        # Goto the account and edit it to see the account opening explanation
        And I click on "account-9911"
        Then I click on "edit-account-details"
        And the "account_openingDateExplanation" field should contain "Test One"
        # Now fill in the account transactions and closing balance.
        Then I fill in the following:
            | transactions_moneyIn_0_amount       | 1 |
            | transactions_moneyOut_0_amount      | 1 |
        And I press "transactions_saveMoneyOut"
        Then I fill in the following:
            | accountBalance_closingDate_day   | 1 | 
            | accountBalance_closingDate_month | 1 | 
            | accountBalance_closingDate_year  | 2015 | 
            | accountBalance_closingBalance    | 100.00 |
        And I press "accountBalance_save"
        And I click on "edit-account-details"
        Then the "account_openingDateExplanation" field should contain "Test One"
        And I fill in the following:
            | account_openingDateExplanation | Test Two |
        Then I press "account_save"
        And I click on "edit-account-details"
        Then the "account_openingDateExplanation" field should contain "Test Two"