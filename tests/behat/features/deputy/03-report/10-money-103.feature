Feature: Report money 103
# to save time, 103 money section is tested by using snapshots inside the 102 journey

  @deputy
  Scenario: money transactions 103 start
    # restore status pre money-102
    Given I load the application status from "money-transactions-before"
    And I change the report 1 type to "103"

  @deputy
  Scenario: money in 103
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-money_in_short, start"
    # categories
    And the step with the following values CAN be submitted:
      | money_short_moneyShortCategoriesIn_0_present | 1 |
      | money_short_moneyShortCategoriesIn_5_present | 1 |
    # chose "no records"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_moneyTransactionsShortInExist_1 | no |
        # summary page check
    And each text should be present in the corresponding region:
      | State pension and benefits       | categories    |
      | Compensations and damages awards | categories    |
      | No                               | records-exist |
        # select there are records (from summary page link)
    Given I click on "edit" in the "records-exist" region
    And the step with the following values CAN be submitted:
      | yes_no_moneyTransactionsShortInExist_0 | yes |
        # add transaction n.1 (and validate form)
    And the step with the following values CANNOT be submitted:
      | money_short_transaction_description |  | [ERR] |
      | money_short_transaction_amount      |  | [ERR] |
    And the step with the following values CANNOT be submitted:
      | money_short_transaction_description |                | [ERR] |
      | money_short_transaction_amount      | invalid number | [ERR] |
    And the step with the following values CANNOT be submitted:
      | money_short_transaction_description |     | [ERR] |
      | money_short_transaction_amount      | 999 | [ERR] |
    And the step with the following values CAN be submitted:
      | money_short_transaction_description | december salary |
      | money_short_transaction_amount      | 1400            |
        # add transaction n.2
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | money_short_transaction_description | january salary |
      | money_short_transaction_amount      | 1500           |
        # add another: no
    And I choose "no" when asked for adding another record
        # check record in summary page
    And each text should be present in the corresponding region:
      | december salary | transaction-december-salary |
      | £1,400.00       | transaction-december-salary |
      | january salary  | transaction-january-salary  |
      | £1,500.00       | transaction-january-salary  |
      | £2,900.00       | transaction-total           |
        # remove transaction n.2
    When I click on "delete" in the "transaction-january-salary" region
    Then I should not see the "transaction-january-salary" region
        # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
        # edit transaction n.1
    When I click on "edit" in the "transaction-december-salary" region
    Then the following fields should have the corresponding values:
      | money_short_transaction_description | december salary |
      | money_short_transaction_amount      | 1,400.00        |
    And the step with the following values CAN be submitted:
      | money_short_transaction_description | november salary |
      | money_short_transaction_amount      | 1,450.00        |
    And each text should be present in the corresponding region:
      | 1,450.00 | transaction-november-salary |


  @deputy
  Scenario: money out 103
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-money_out_short, start"
    # categories
    And the step with the following values CAN be submitted:
      | money_short_moneyShortCategoriesOut_0_present | 1 |
      | money_short_moneyShortCategoriesOut_4_present | 1 |
    # chose "no records"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_moneyTransactionsShortOutExist_1 | no |
        # summary page check
    And each text should be present in the corresponding region:
      | Accommodation costs      | categories    |
      | Cly's personal allowance | categories    |
      | No                       | records-exist |
        # select there are records (from summary page link)
    Given I click on "edit" in the "records-exist" region
    And the step with the following values CAN be submitted:
      | yes_no_moneyTransactionsShortOutExist_0 | yes |
        # add transaction n.1 (and validate form)
    And the step with the following values CANNOT be submitted:
      | money_short_transaction_description |  | [ERR] |
      | money_short_transaction_amount      |  | [ERR] |
    And the step with the following values CANNOT be submitted:
      | money_short_transaction_description |                | [ERR] |
      | money_short_transaction_amount      | invalid number | [ERR] |
    And the step with the following values CANNOT be submitted:
      | money_short_transaction_description |     | [ERR] |
      | money_short_transaction_amount      | 999 | [ERR] |
    And the step with the following values CAN be submitted:
      | money_short_transaction_description | december rent |
      | money_short_transaction_amount      | 1401            |
        # add transaction n.2
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | money_short_transaction_description | january rent |
      | money_short_transaction_amount      | 1501           |
        # add another: no
    And I choose "no" when asked for adding another record
        # check record in summary page
    And each text should be present in the corresponding region:
      | december rent | transaction-december-rent |
      | £1,401.00       | transaction-december-rent |
      | january rent  | transaction-january-rent  |
      | £1,501.00       | transaction-january-rent  |
      | £2,900.00       | transaction-total           |
        # remove transaction n.2
    When I click on "delete" in the "transaction-january-rent" region
    Then I should not see the "transaction-january-rent" region
        # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
        # edit transaction n.1
    When I click on "edit" in the "transaction-december-rent" region
    Then the following fields should have the corresponding values:
      | money_short_transaction_description | december rent |
      | money_short_transaction_amount      | 1,401.00        |
    And the step with the following values CAN be submitted:
      | money_short_transaction_description | november rent |
      | money_short_transaction_amount      | 1,451.00        |
    And each text should be present in the corresponding region:
      | 1,451.00 | transaction-november-rent |


  @deputy
  Scenario: money transactions 103 end
    # restore status after money 102 finished
    Given I load the application status from "money-transactions-after"
    And I change the report 1 type to "102"







