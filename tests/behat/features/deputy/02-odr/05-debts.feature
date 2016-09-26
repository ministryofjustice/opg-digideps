Feature: odr / debts

  @odr
  Scenario: ODR  debt
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-assets, debts-tab"
    And I save the page as "odr-debts-empty"
    # empty form
    When I press "odr_debt_save"
    Then the following fields should have an error:
      | odr_debt_hasDebts_0 |
      | odr_debt_hasDebts_1 |
    # "no"
    When I fill in the following:
      |  odr_debt_hasDebts_1 | no |
    And I press "odr_debt_save"
    Then the form should be valid
    When I click on "debts-tab"
    Then the following fields should have the corresponding values:
      |  odr_debt_hasDebts_1 | no |
    # "yes", no debts
    When I fill in the following:
      |  odr_debt_hasDebts_0 | yes |
      |  odr_debt_debts_0_amount |  |
      |  odr_debt_debts_1_amount |  |
      |  odr_debt_debts_2_amount |  |
      |  odr_debt_debts_3_amount |  |
    And I press "odr_debt_save"
    Then the form should be invalid
    # "yes", invalid debts values, incuding missing textarea
    When I fill in the following:
      |  odr_debt_hasDebts_0 | yes |
      |  odr_debt_debts_0_amount | abc |
      |  odr_debt_debts_1_amount | -1 |
      |  odr_debt_debts_2_amount | 1,1,1 |
      |  odr_debt_debts_3_amount | 1 |
    And I press "odr_debt_save"
    Then the following fields should have an error:
      |  odr_debt_debts_0_amount |
      |  odr_debt_debts_1_amount |
      |  odr_debt_debts_2_amount |
      |  odr_debt_debts_3_moreDetails |
    # "yes", valid values
    When I fill in the following:
      |  odr_debt_hasDebts_0 | yes |
      |  odr_debt_debts_0_amount | 123456 |
      |  odr_debt_debts_1_amount | 1234.2 |
      |  odr_debt_debts_2_amount | 1,123 |
      |  odr_debt_debts_3_amount | 01234.10 |
      |  odr_debt_debts_3_moreDetails | d3md |
    And I press "odr_debt_save"
    Then the form should be valid
    # assert saved
    When I click on "debts-tab"
    Then the following fields should have the corresponding values:
      |  odr_debt_hasDebts_0 | yes |
      |  odr_debt_debts_0_amount | 123,456.00 |
      |  odr_debt_debts_1_amount | 1,234.20 |
      |  odr_debt_debts_2_amount | 1,123.00 |
      |  odr_debt_debts_3_amount | 1,234.10 |
      |  odr_debt_debts_3_moreDetails | d3md |