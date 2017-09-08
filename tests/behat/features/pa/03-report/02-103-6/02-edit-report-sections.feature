Feature: PA user edits 103-6 report sections

  @103-6
  Scenario: PA 103-6 money in
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-money_in_short, start"
    And the step with the following values CAN be submitted:
      | money_short_moneyShortCategoriesIn_0_present | 1 |
      | money_short_moneyShortCategoriesIn_5_present | 1 |
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_moneyTransactionsShortInExist_1 | no |
    And each text should be present in the corresponding region:
      | State pension and benefits       | categories    |
      | Compensations and damages awards | categories    |
      | No                               | records-exist |
    Given I click on "edit" in the "records-exist" region
    And the step with the following values CAN be submitted:
      | yes_no_moneyTransactionsShortInExist_0 | yes |
    And the step with the following values CAN be submitted:
      | money_short_transaction_description | december salary |
      | money_short_transaction_amount      | 1400            |
    And I choose "no" when asked for adding another record
          # check record in summary page
    And each text should be present in the corresponding region:
      | december salary | transaction-december-salary |
      | £1,400.00       | transaction-december-salary |
      | £1,400.00       | transaction-total           |

  @103-6
  Scenario: PA 103-6 money out
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-money_out_short, start"
    And the step with the following values CAN be submitted:
      | money_short_moneyShortCategoriesOut_0_present | 1 |
      | money_short_moneyShortCategoriesOut_4_present | 1 |
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_moneyTransactionsShortOutExist_1 | no |
    And each text should be present in the corresponding region:
      | Accommodation costs | categories    |
      | personal allowance  | categories    |
      | No                  | records-exist |
    Given I click on "edit" in the "records-exist" region
    And the step with the following values CAN be submitted:
      | yes_no_moneyTransactionsShortOutExist_0 | yes |
    And the step with the following values CAN be submitted:
      | money_short_transaction_description | december rent |
      | money_short_transaction_amount      | 1401          |
    And I choose "no" when asked for adding another record
    And each text should be present in the corresponding region:
      | december rent | transaction-december-rent |
      | £1,401.00     | transaction-december-rent |
      | £1,401.00     | transaction-total         |
    And I save the application status into "pa-report-103-completed"

  @103-6
  Scenario: PA 103-6 debts
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-debts, start"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_hasDebts_1 | no |
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000011" region
    And I click on "edit-bank_accounts, start"
    And the step with the following values CAN be submitted:
      | account_accountType_0 | current |
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
    And I choose "no" when asked for adding another record

  @103-6
  Scenario: PA 103-6 fees and expenses
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-pa_fee_expense, start"
    Given the step cannot be submitted without making a selection
    And the step with the following values cannot be submitted:
      | fee_exist_hasFees_1 | no |
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | fee_exist_reasonForNoFees | Some reason for no fees|
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_paidForAnything_1 | no |
    And each text should be present in the corresponding region:
      | no                            | no-contacts        |
      | Some reason for no fees       | reason-no-fees     |
      | no                            | paid-for-anything  |

  @103-6
  Scenario: PA 103-6 gifts
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-gifts, start"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_giftsExist_1 | no |
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000011" region
    And I click on "edit-assets, start"
    And the step with the following values CAN be submitted:
      | yes_no_noAssetToAdd_1 | 1 |

  @103-6
  Scenario: PA 103-6 accounts add current account
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

  @103-6
  Scenario: PA 103-6 assets
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-assets, start"
      # chose "no records"
    And the step with the following values CAN be submitted:
      | yes_no_noAssetToAdd_1 | 1 |






