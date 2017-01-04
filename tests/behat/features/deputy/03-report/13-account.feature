Feature: deputy / report / account

  @deputy
  Scenario: add account
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports,report-2016-open, edit-bank_accounts, start"
      # step 1
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | account_accountType_0 | current |
    # add account n.1
    Then the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | account_bank                      | x | [ERR] |
      | account_accountNumber             | x | [ERR] |
      | account_sortCode_sort_code_part_1 | g | [ERR] |
      | account_sortCode_sort_code_part_2 | h | [ERR] |
      | account_sortCode_sort_code_part_3 |   | [ERR] |
    And the step with the following values CAN be submitted:
      | account_bank                      | HSBC - main account |
      | account_accountNumber             | a876                |
      | account_sortCode_sort_code_part_1 | 08                  |
      | account_sortCode_sort_code_part_2 | 77                  |
      | account_sortCode_sort_code_part_3 | 00                  |
      | account_isJointAccount_1          | no                  |
    And the step with the following values CANNOT be submitted:
      | account_openingBalance | invalid | [ERR] |
      | account_closingBalance | invalid | [ERR] |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 100.40 |
      | account_closingBalance | 200.50 |
    #add another: yes
    And I choose "yes" when asked for adding another record
    # add account n.2
    And the step with the following values CAN be submitted:
      | account_accountType_0 | current |
    And the step with the following values CAN be submitted:
      | account_bank                      | temp2 |
      | account_accountNumber             | 1234  |
      | account_sortCode_sort_code_part_1 | 11    |
      | account_sortCode_sort_code_part_2 | 11    |
      | account_sortCode_sort_code_part_3 | 11    |
      | account_isJointAccount_1          | no    |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 123 |
      | account_closingBalance | 123 |
    #add another: no
    And I choose "no" when asked for adding another record
        # check record in summary page
    And each text should be present in the corresponding region:
      | HSBC - main account | account-a876 |
      | Current account     | account-a876 |
      | £100.40             | account-a876 |
      | £200.50             | account-a876 |
    ## remove account 1234
    When I click on "delete" in the "account-1234" region
    Then I should not see the "account-1234" region
    # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
        # edit account n.1
    When I click on "edit" in the "account-a876" region
    Then the following fields should have the corresponding values:
      | account_accountType_0 | current |
    And the step with the following values CAN be submitted:
      | account_accountType_0 | savings |
    Then the following fields should have the corresponding values:
      | account_bank                      | HSBC - main account |
      | account_accountNumber             | a876                |
      | account_sortCode_sort_code_part_1 | 08                  |
      | account_sortCode_sort_code_part_2 | 77                  |
      | account_sortCode_sort_code_part_3 | 00                  |
      | account_isJointAccount_1          | no                  |
    And the step with the following values CAN be submitted:
      | account_bank                      | HSBC - saving account |
      | account_accountNumber             | a877                  |
      | account_sortCode_sort_code_part_1 | 11                    |
      | account_sortCode_sort_code_part_2 | 22                    |
      | account_sortCode_sort_code_part_3 | 33                    |
      | account_isJointAccount_0          | yes                   |
    Then the following fields should have the corresponding values:
      | account_openingBalance | 100.40 |
      | account_closingBalance | 200.50 |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 101.40 |
      | account_closingBalance | 201.50 |
    And each text should be present in the corresponding region:
      | HSBC - saving account | account-a877 |
      | Saving account        | account-a877 |
      | £101.40               | account-a877 |
      | £201.50               | account-a877 |
    # check step 2 changes depending on step1
    When I click on "add"
    And the step with the following values CAN be submitted:
      | account_accountType_0 | cfo |
    Then I should see an "#account_accountNumber" element
    Then I should not see an "#account_bank" element
    Then I should not see an "#account_sortCode_sort_code_part_1" element
