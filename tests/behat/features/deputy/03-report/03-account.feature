Feature: deputy / report / account
    
    @deputy
    Scenario: add account
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the accounts page of the "2016" report
        And I save the page as "report-account-empty"
        # empty form
        When I follow "add-account"
        And I press "account_save"
        And I save the page as "report-account-add-error"
        Then the following fields should have an error:
            | account_bank |
            | account_accountNumber|
            | account_accountType |
            | account_sortCode_sort_code_part_1 |
            | account_sortCode_sort_code_part_2 |
            | account_sortCode_sort_code_part_3 |
            | account_openingBalance |
        # test validators
        When I fill in the following:
            | account_bank    | x |
            | account_accountNumber | x |
            | account_accountType | current | 
            | account_sortCode_sort_code_part_1 | g |
            | account_sortCode_sort_code_part_2 | h |
            | account_sortCode_sort_code_part_3 |  |
            | account_openingBalance  | invalid |
            | account_closingBalance  | invalid |
        And I press "account_save"
        Then the following fields should have an error:
            | account_bank    |
            | account_accountNumber | 
            | account_sortCode_sort_code_part_1 | 
            | account_sortCode_sort_code_part_2 |
            | account_sortCode_sort_code_part_3 | 
            | account_openingBalance  |
            | account_closingBalance  |
        # right values
        And I fill in the following:
            | account_bank    | HSBC - main account |
            | account_accountNumber | 0876 |
            | account_accountType | current | 
            | account_sortCode_sort_code_part_1 | 08 |
            | account_sortCode_sort_code_part_2 | 77 |
            | account_sortCode_sort_code_part_3 | 00 |
            | account_openingBalance  | 1155 |
            | account_closingBalance  | 1,155.00 |
        And I press "account_save"
        And I save the page as "report-account-list"
        #Then the response status code should be 200
        And the form should be valid
        And the URL should match "/report/\d+/accounts"
        And I should see "HSBC - main account" in the "list-accounts" region
        When I click on "account-0876"
        Then I should not see the "opening-balance-explanation" region
        # refresh page and check values
        When I follow "overview-button"
        Then I follow "edit-accounts"
        And I should see "HSBC - main account" in the "list-accounts" region
        And I should see "0876" in the "list-accounts" region
        And I should see "£1,155.00" in the "list-accounts" region

    @deputy
    Scenario: edit 1st account (HSBC - main account)
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the account "0876" page of the "2016" report
        And I save the page as "report-account-edit-start"
        # assert fields are filled in from db correctly
        Then the following fields should have the corresponding values:
            | account_bank    | HSBC - main account |
            | account_accountNumber | 0876 |
            | account_accountType | current | 
            | account_sortCode_sort_code_part_1 | 08 |
            | account_sortCode_sort_code_part_2 | 77 |
            | account_sortCode_sort_code_part_3 | 00 |
            | account_openingBalance  | 1,155.00 |
            | account_closingBalance  | 1,155.00 |
        # right values
        When I fill in the following:
            | account_bank    | HSBC main account |
            | account_accountNumber | 0876 |
            | account_accountType | current | 
            | account_sortCode_sort_code_part_1 | 12 |
            | account_sortCode_sort_code_part_2 | 34 |
            | account_sortCode_sort_code_part_3 | 56 |
            | account_openingBalance  | 1,150 |
            | account_closingBalance  | 1,155.00 |
        And I press "account_save"
        # check values are saved
        When I click on "account-0876"
        Then the following fields should have the corresponding values:
            | account_bank    | HSBC main account |
            | account_accountNumber | 0876 |
            | account_sortCode_sort_code_part_1 | 12 |
            | account_sortCode_sort_code_part_2 | 34 |
            | account_sortCode_sort_code_part_3 | 56 |
            | account_openingBalance  | 1,150.00 |
            | account_closingBalance  | 1,155.00 |
        And I save the page as "report-account-edit-reloaded"


    @deputy
    Scenario: add another account (9999) and delete it
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I add the following bank account:
            | bank    | temp  | | |
            | accountNumber | 9999 | | |
            | accountType | isa | | |
            | sortCode | 11 | 22 | 33 |
            | openingBalance  | 100 | | |
            | closingBalance  | 22 | | |
        When I click on "account-9999"
        # delete and cancel
        And I click on "delete-button"
        Then I should not see the "account-9999" link

    @deputy
    Scenario: add another account (8888) 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I add the following bank account:
            | bank    | temp  | | |
            | accountNumber | 8888 | | |
            | accountType | isa | | |
            | sortCode | 11 | 22 | 33 |
            | openingBalance  | 0 | | |
            | closingBalance  | 0 | | |