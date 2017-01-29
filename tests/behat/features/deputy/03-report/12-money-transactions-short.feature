Feature: deputy / report / money transactions 103
# to save time, 103 money section is tested by using snapshots inside the 102 journey

  @deputy
  Scenario: money in 103
    # restore 102 pre-status
    Given I load the application status from "money-transactions-before"
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-money_in_short, start"
    # categories
    And the step with the following values CAN be submitted:
      | income_benefits_moneyShortCategoriesIn_0_present            | 1 |
      | form-group-income_benefits_moneyShortCategoriesIn_6_present | 1 |
    And I click on "reports, report-2016, edit-money_in, start"
    # chose "no records"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | transaction_exist_moneyTransactionsShortInExist_1 | no |
        # summary page check
    And each text should be present in the corresponding region:
      | No | records-exist |
        # select there are records (from summary page link)
    Given I click on "edit" in the "records-exist" region
    And the step with the following values CAN be submitted:
      | transaction_exist_moneyTransactionsShortInExist_0 | yes |
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
      | december salary | transaction-december-salary   |
      | £1,400.00       | transaction-december-salary   |
      | january salary  | transaction-january-salary    |
      | £1,500.00       | transaction-january-salary    |
      | £49.00          | transaction-transaction-total |
        # remove transaction n.2
    When I click on "delete" in the "january-salary" region
    Then I should not see the "january-salary" region
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
    # TODO

    # restore 102 status
    Given I load the application status from "money-transactions-after"







