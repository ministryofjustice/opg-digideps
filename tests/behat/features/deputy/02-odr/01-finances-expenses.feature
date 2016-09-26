Feature: odr / finance expenses

  @odr
  Scenario: ODR expenses
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-finances"
    And I save the page as "odr-expenses-empty"
    # empty form
    When I press "odr_expenses_save"
    Then the following fields should have an error:
      | odr_expenses_paidForAnything_0 |
      | odr_expenses_paidForAnything_1 |
    # no
    When I fill in the following:
      |  odr_expenses_paidForAnything_1 | no |
    And I press "odr_expenses_save"
    Then the form should be valid
    When I click on "finance-expenses"
    Then the following fields should have the corresponding values:
      |  odr_expenses_paidForAnything_1 | no |
    # yes: wrong
    When I fill in the following:
      |  odr_expenses_paidForAnything_0 | yes |
      |  odr_expenses_expenses_0_explanation | yes |
      |  odr_expenses_expenses_0_amount | invalid |
    And I press "odr_expenses_save"
    Then the following fields should have an error:
      | odr_expenses_expenses_0_amount |
    # yes
    When I fill in the following:
      |  odr_expenses_paidForAnything_0 | yes |
      |  odr_expenses_expenses_0_explanation | yes |
      |  odr_expenses_expenses_0_amount | 1234.01 |
    And I press "odr_expenses_save"
    Then the form should be valid
    When I click on "finance-expenses"
    Then the following fields should have the corresponding values:
      |  odr_expenses_paidForAnything_0 | yes |
      |  odr_expenses_expenses_0_explanation | yes |
      |  odr_expenses_expenses_0_amount | 1,234.01 |
    And I save the page as "odr-expenses-done"
