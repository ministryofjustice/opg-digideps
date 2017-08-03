Feature: Report accounts

  @deputy
  Scenario: add account
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-bank_accounts, start"
    # step 1
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | account_accountType_0 | current |
    # add account n.1 (current)
    Then the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | account_bank                      | x | [ERR] |
      | account_accountNumber             | x | [ERR] |
      | account_sortCode_sort_code_part_1 | g | [ERR] |
      | account_sortCode_sort_code_part_2 | h | [ERR] |
      | account_sortCode_sort_code_part_3 |   | [ERR] |
    And the step with the following values CAN be submitted:
      | account_bank                      | HSBC - main account |
      | account_accountNumber             | 01ca                |
      | account_sortCode_sort_code_part_1 | 11                  |
      | account_sortCode_sort_code_part_2 | 22                  |
      | account_sortCode_sort_code_part_3 | 33                  |
      | account_isJointAccount_1          | no                  |
    And the step with the following values CANNOT be submitted:
      | account_openingBalance | invalid | [ERR] |
      | account_closingBalance | invalid | [ERR] |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 100.40 |
      | account_closingBalance | 200.50 |
    # add another: yes
    And I choose "yes" when asked for adding another record
    # add account n.2 (cfo)
    And the step with the following values CAN be submitted:
      | account_accountType_0 | cfo |
    And the step with the following values CAN be submitted:
      | account_accountNumber    | 11cf |
      | account_isJointAccount_1 | no   |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 234 |
      | account_closingBalance | 235 |
    # add another: yes
    And I choose "yes" when asked for adding another record
    # add account n.3 (temp)
    And the step with the following values CAN be submitted:
      | account_accountType_0 | current |
    And the step with the following values CAN be submitted:
      | account_bank                      | temp2 |
      | account_accountNumber             | temp  |
      | account_sortCode_sort_code_part_1 | 33    |
      | account_sortCode_sort_code_part_2 | 33    |
      | account_sortCode_sort_code_part_3 | 33    |
      | account_isJointAccount_1          | no    |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 123 |
      | account_closingBalance | 123 |
    # add another: no
    And I choose "no" when asked for adding another record
    # check record in summary page
    And each text should be present in the corresponding region:
      | HSBC - main account        | account-01ca |
      | Current account            | account-01ca |
      | 112233                     | account-01ca |
      | £100.40                    | account-01ca |
      | £200.50                    | account-01ca |
      | Court funds office account | account-11cf |
      | £234.00                    | account-11cf |
      | £235.00                    | account-11cf |
    # remove account
    When I click on "delete" in the "account-temp" region
    Then I should not see the "account-temp" region
    # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
    # edit account n.1
    When I click on "edit" in the "account-01ca" region
    Then the following fields should have the corresponding values:
      | account_accountType_0 | current |
    And the step with the following values CAN be submitted:
      | account_accountType_0 | savings |
    Then the following fields should have the corresponding values:
      | account_bank                      | HSBC - main account |
      | account_accountNumber             | 01ca                |
      | account_sortCode_sort_code_part_1 | 11                  |
      | account_sortCode_sort_code_part_2 | 22                  |
      | account_sortCode_sort_code_part_3 | 33                  |
      | account_isJointAccount_1          | no                  |
    And the step with the following values CAN be submitted:
      | account_bank                      | HSBC - saving account |
      | account_accountNumber             | 02ca                  |
      | account_sortCode_sort_code_part_1 | 44                    |
      | account_sortCode_sort_code_part_2 | 55                    |
      | account_sortCode_sort_code_part_3 | 66                    |
      | account_isJointAccount_0          | yes                   |
    Then the following fields should have the corresponding values:
      | account_openingBalance | 100.40 |
      | account_closingBalance | 200.50 |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 101.40 |
      | account_closingBalance | 201.50 |
    And each text should be present in the corresponding region:
      | HSBC - saving account | account-02ca |
      | Saving account        | account-02ca |
      | 445566                | account-02ca |
      | £101.40               | account-02ca |
      | £201.50               | account-02ca |
