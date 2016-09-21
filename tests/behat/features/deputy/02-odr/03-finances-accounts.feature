Feature: odr / finance accounts

 @odr
  Scenario: odr add account
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-finances, finance-banks"
    And I save the page as "odr-report-account-empty"
      # empty form
    When I follow "add-account"
    And I press "account_save"
    And I save the page as "odr-report-account-add-error"
    Then the following fields should have an error:
      | account_bank |
      | account_accountNumber|
      | account_accountType |
      | account_sortCode_sort_code_part_1 |
      | account_sortCode_sort_code_part_2 |
      | account_sortCode_sort_code_part_3 |
      | account_balanceOnCourtOrderDate |
      # test validators
    When I fill in the following:
      | account_bank    | x |
      | account_accountNumber | x |
      | account_accountType | current |
      | account_sortCode_sort_code_part_1 | g |
      | account_sortCode_sort_code_part_2 | h |
      | account_sortCode_sort_code_part_3 |  |
      | account_balanceOnCourtOrderDate  | invalid |
    And I press "account_save"
    Then the following fields should have an error:
      | account_bank    |
      | account_accountNumber |
      | account_sortCode_sort_code_part_1 |
      | account_sortCode_sort_code_part_2 |
      | account_sortCode_sort_code_part_3 |
      | account_balanceOnCourtOrderDate  |
      # right values
    And I fill in the following:
      | account_bank    | HSBC - main account |
      | account_accountNumber | 0876 |
      | account_accountType | current |
      | account_sortCode_sort_code_part_1 | 08 |
      | account_sortCode_sort_code_part_2 | 77 |
      | account_sortCode_sort_code_part_3 | 00 |
      | account_balanceOnCourtOrderDate  | 1155 |
    And I press "account_save"
    And I save the page as "odr-report-account-list"
      #Then the response status code should be 200
    And the form should be valid
    And I should see "HSBC - main account" in the "list-accounts" region
    When I click on "account-0876"
    Then I should not see the "opening-balance-explanation" region
      # refresh page and check values
    And I click on "finance-banks"
    And I should see "HSBC - main account" in the "list-accounts" region
    And I should see "0876" in the "list-accounts" region
    And I should see "£1,155.00" in the "list-accounts" region

 @odr
  Scenario: odr edit 1st account (HSBC - main account)
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-finances, finance-banks, account-0876"
    And I save the page as "odr-report-account-edit-start"
      # assert fields are filled in from db correctly
    Then the following fields should have the corresponding values:
      | account_bank    | HSBC - main account |
      | account_accountNumber | 0876 |
      | account_accountType | current |
      | account_sortCode_sort_code_part_1 | 08 |
      | account_sortCode_sort_code_part_2 | 77 |
      | account_sortCode_sort_code_part_3 | 00 |
      | account_balanceOnCourtOrderDate  | 1,155.00 |
      # right values
    When I fill in the following:
      | account_bank    | HSBC main account |
      | account_accountNumber | 0876 |
      | account_accountType | current |
      | account_sortCode_sort_code_part_1 | 12 |
      | account_sortCode_sort_code_part_2 | 34 |
      | account_sortCode_sort_code_part_3 | 56 |
      | account_balanceOnCourtOrderDate  | 1,158 |
    And I press "account_save"
    Then I should not see a "#account_isClosed" element
      # check values are saved
    When I click on "account-0876"
    Then the following fields should have the corresponding values:
      | account_bank    | HSBC main account |
      | account_accountNumber | 0876 |
      | account_sortCode_sort_code_part_1 | 12 |
      | account_sortCode_sort_code_part_2 | 34 |
      | account_sortCode_sort_code_part_3 | 56 |
      | account_balanceOnCourtOrderDate  | 1,158.00 |
    And I save the page as "odr-report-account-edit-reloaded"


 @odr
  Scenario: odr  add another account, and delete it
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-finances, finance-banks"
    And I save the page as "odr-accounts-empty"
    And I follow "add-account"
    And I fill in the following:
      | account_bank    | temp |
      | account_accountNumber | 9999 |
      | account_accountType | isa |
      | account_sortCode_sort_code_part_1 | 11 |
      | account_sortCode_sort_code_part_2 | 22 |
      | account_sortCode_sort_code_part_3 | 33 |
      | account_balanceOnCourtOrderDate  | 100 |
    And I press "account_save"
    Then I should not see a "#account_isClosed" element
      #
      # delete
      #
    When I click on "account-9999"
    And I click on "delete-button"
    Then I should not see the "account-9999" link
    And I save the page as "odr-accounts-one-added-one-deleted"
