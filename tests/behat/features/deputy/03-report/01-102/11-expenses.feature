Feature: Report deputy expenses

  @deputy
  Scenario: deputy expenses
    Given I am logged in as "behat-user@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "report-start, edit-deputy_expenses, start"
    # chose "no records"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_paidForAnything_1 | no |
        # summary page check
    And each text should be present in the corresponding region:
      | No | paid-for-anything |
        # select there are records (from summary page link)
    Given I click on "edit" in the "paid-for-anything" region
    And the step with the following values CAN be submitted:
      | yes_no_paidForAnything_0 | yes |
        # add expense n.1 (and validate form)
    And I should see an "select#expenses_single_bankAccountId" element
    And the step with the following values CANNOT be submitted:
      | expenses_single_explanation |  | [ERR] |
      | expenses_single_amount      |  | [ERR] |
    And the step with the following values CANNOT be submitted:
      | expenses_single_explanation |                | [ERR] |
      | expenses_single_amount      | invalid number | [ERR] |
    And the step with the following values CANNOT be submitted:
      | expenses_single_explanation |                | [ERR] |
      | expenses_single_amount      | 10000000.01 | [ERR] |
    And the "#error-summary" element should contain "10,000,000"
    And the step with the following values CANNOT be submitted:
      | expenses_single_explanation |                | [ERR] |
      | expenses_single_amount      | 0.0 | [ERR] |
    And I select "HSBC - saving account - Savings account (****02ca)" from "expenses_single_bankAccountId"
    And the step with the following values CAN be submitted:
      | expenses_single_explanation | taxi from hospital on 3 november |
      | expenses_single_amount      | 35                               |
        # add expense n.2
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | expenses_single_explanation | food for client on 3 november |
      | expenses_single_amount      | 14                            |
        # add another: no
    And I choose "no" when asked for adding another record
        # check record in summary page
    And each text should be present in the corresponding region:
      | taxi from hospital on 3 november | expense-taxi-from-hospital-on-3-november |
      | £35.00                           | expense-taxi-from-hospital-on-3-november |
      | food for client on 3 november    | expense-food-for-client-on-3-november    |
      | £14.00                           | expense-food-for-client-on-3-november    |
      | £49.00                           | expense-total    |
        # remove expense n.2
    When I click on "delete" in the "expense-food-for-client-on-3-november" region
    Then I should not see the "expense-food-for-client-on-3-november" region
        # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
        # edit expense n.1
    When I click on "edit" in the "expense-taxi-from-hospital-on-3-november" region
    Then the following fields should have the corresponding values:
      | expenses_single_explanation | taxi from hospital on 3 november |
      | expenses_single_amount      | 35.00                            |
    And I should see "HSBC - saving account - Savings account (****02ca)" in the "#expenses_single_bankAccountId" element
    And I select "Court Funds Office account (****11cf)" from "expenses_single_bankAccountId"
    And the step with the following values CAN be submitted:
      | expenses_single_explanation | taxi from hospital on 4 november |
      | expenses_single_amount      | 45                               |
    And each text should be present in the corresponding region:
      | taxi from hospital on 4 november | expense-taxi-from-hospital-on-4-november |
      | £45.00                           | expense-taxi-from-hospital-on-4-november |
