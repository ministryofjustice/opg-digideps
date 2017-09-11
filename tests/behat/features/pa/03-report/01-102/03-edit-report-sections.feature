Feature: PA user edits report sections

  Scenario: PA 102 deputy expenses (No fees exist)
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-pa_fee_expense, start"
    # chose "no option"
    Given the step cannot be submitted without making a selection
    And the step with the following values cannot be submitted:
      | fee_exist_hasFees_1 | no |
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | fee_exist_reasonForNoFees | Some reason for no fees|
    # "Fees outside practice direction" question
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_paidForAnything_1 | no |
    # check record in summary page
    And each text should be present in the corresponding region:
      | no                            | no-contacts        |
      | Some reason for no fees       | reason-no-fees     |
      | no                            | paid-for-anything  |

  Scenario: PA 102 gifts
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-gifts, start"
    # chose "no records"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_giftsExist_1 | no |

  Scenario: PA 102 assets
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-assets, start"
      # chose "no records"
    And the step with the following values CAN be submitted:
      | yes_no_noAssetToAdd_1 | 1 |

  Scenario: PA 102  debts
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-debts, start"
      # chose "no records"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_hasDebts_1 | no |

  Scenario: PA 102 add current account
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-bank_accounts, start"
    # step 1
    And the step with the following values CAN be submitted:
      | account_accountType_0 | current |
    # add account n.1 (current)
    And I should see an "input#account_bank" element
    And I should see an "input#account_sortCode_sort_code_part_1" element
    And I should see an "input#account_sortCode_sort_code_part_2" element
    And I should see an "input#account_sortCode_sort_code_part_3" element
    And the step with the following values CAN be submitted:
      | account_bank                      | HSBC - main account |
      | account_accountNumber             | 01ca                |
      | account_sortCode_sort_code_part_1 | 11                  |
      | account_sortCode_sort_code_part_2 | 22                  |
      | account_sortCode_sort_code_part_3 | 33                  |
      | account_isJointAccount_1          | no                  |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 100.40 |
      | account_closingBalance | 100.40 |
    # add another: no
    And I choose "no" when asked for adding another record

  Scenario: PA 102 add postoffice account (no sort code, no bank name)
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-bank_accounts, add"
    # step 1
    And the step with the following values CAN be submitted:
      | account_accountType_0 | postoffice |
    # add account n.1 (current)
    And the step with the following values CAN be submitted:
      | account_accountNumber             | 2222                |
      | account_isJointAccount_1          | no                  |
    And I should not see an "input#account_bank" element
    And I should not see an "input#account_sortCode_sort_code_part_1" element
    And I should not see an "input#account_sortCode_sort_code_part_2" element
    And I should not see an "input#account_sortCode_sort_code_part_3" element
    And the step with the following values CAN be submitted:
      | account_openingBalance | 100.40 |
      | account_closingBalance | 100.40 |
    # add another: no
    And I choose "no" when asked for adding another record

  Scenario: PA 102 add no sortcode account (still requires bank name)
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-bank_accounts, add"
    # step 1
    And the step with the following values CAN be submitted:
      | account_accountType_0 | other_no_sortcode |
    # add account n.1 (current)
    And I should see an "input#account_bank" element
    And I should not see an "input#account_sortCode_sort_code_part_1" element
    And I should not see an "input#account_sortCode_sort_code_part_2" element
    And I should not see an "input#account_sortCode_sort_code_part_3" element
    And the step with the following values CAN be submitted:
      | account_bank                      | Bank of Jack        |
      | account_accountNumber             | 3333                |
      | account_isJointAccount_1          | no                  |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 100.40 |
      | account_closingBalance | 100.40 |
    # add another: no
    And I choose "no" when asked for adding another record

  Scenario: PA 102 deletes bank account
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-bank_accounts"
    And I click on "delete" in the "account-2222" region
    Then I should see "Bank account deleted"
    When I click on "delete" in the "account-3333" region
    Then I should see "Bank account deleted"

  Scenario: PA 102 money in
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-money_in, start"
    # add transaction n.1 and check validation
    And the step with the following values CAN be submitted:
      | account_group_0 | pensions |
    And the step with the following values CAN be submitted:
      | account_category_0 | state-pension |
    And the step with the following values CAN be submitted:
      | account_description | pension received |
      | account_amount      | 50.00         |
    # add another: no
    And I choose "no" when asked for adding another record

  Scenario: PA 102 money out
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-money_out, start"
      # add transaction n.1 and check validation
    And the step with the following values CAN be submitted:
      | account_group_0 | household-bills |
    And the step with the following values CAN be submitted:
      | account_category_0 | broadband |
    And the step with the following values CAN be submitted:
      | account_description | january bill |
      | account_amount      | 50.00     |
      # add another: no
    And I choose "no" when asked for adding another record


  Scenario: PA 102 Report should be submittable
    Given I save the application status into "pa-report-balance-before"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    Then the report should be submittable
    And I save the application status into "pa-report-completed"
