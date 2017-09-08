Feature: PA user edits report sections

  # 103 Report
  Scenario: PA 103 attaches no documents (to enable submission)
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000011" region
    Then the report should not be submittable
    And I click on "edit-documents, start"
  # chose "no documents"
    Then the URL should match "report/\d+/documents/step/1"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | document_wishToProvideDocumentation_1 | no |
  # check no documents in summary page
    Then the URL should match "report/\d+/documents/summary"
    And I should not see the region "document-list"
    
  Scenario: PA 103 money in
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000011" region
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

  Scenario: PA 103 money out
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000011" region
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
    And I save the application status into "pa-report-103-inprogress"
