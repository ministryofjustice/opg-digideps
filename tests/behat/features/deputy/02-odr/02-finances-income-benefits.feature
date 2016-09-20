Feature: odr / finance income benefits

  @odr
  Scenario: income and benefits / state benefits
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-finances, finance-income-benefits"
    And I save the page as "odr-income-benefits-empty"
    # empty
    And I press "odr_income_state_benefits_save"
    Then the form should be valid
    # error
    When I check "odr_income_state_benefits_stateBenefits_11_present"
    And I press "odr_income_state_benefits_save"
    Then the following fields should have an error:
      | odr_income_state_benefits_stateBenefits_11_moreDetails |
    # correct
    When I check "odr_income_state_benefits_stateBenefits_9_present"
    And I check "odr_income_state_benefits_stateBenefits_11_present"
    And I fill in the following:
      |  odr_income_state_benefits_stateBenefits_11_moreDetails | other benefits details |
    And I press "odr_income_state_benefits_save"
    # check saved ok
    When I click on "finance-income-benefits"
    Then the following fields should have the corresponding values:
      |  odr_income_state_benefits_stateBenefits_9_present | 1 |
      |  odr_income_state_benefits_stateBenefits_11_present | 1 |
      |  odr_income_state_benefits_stateBenefits_11_moreDetails | other benefits details |

  @odr
  Scenario: income and benefits / pension
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-finances, finance-income-benefits"
    # empty
    When I press "odr_income_pension_save"
    Then the following fields should have an error:
      | odr_income_pension_receiveStatePension_0 |
      | odr_income_pension_receiveStatePension_1 |
      | odr_income_pension_receiveOtherIncome_0 |
      | odr_income_pension_receiveOtherIncome_1 |
    #  error
    Then I fill in the following:
      |  odr_income_pension_receiveStatePension_1 | no |
      |  odr_income_pension_receiveOtherIncome_0 | yes |
      |  odr_income_pension_receiveOtherIncomeDetails |  |
    And I press "odr_income_pension_save"
    Then the following fields should have an error:
      | odr_income_pension_receiveOtherIncomeDetails |
    # correct
    When I fill in the following:
      |  odr_income_pension_receiveStatePension_1 | no |
      |  odr_income_pension_receiveOtherIncome_0 | yes |
      |  odr_income_pension_receiveOtherIncomeDetails | roi-details |
    And I press "odr_income_pension_save"
    Then the form should be valid
    # check saved ok
    When I click on "finance-income-benefits"
    Then the following fields should have the corresponding values:
      |  odr_income_pension_receiveStatePension_1 | no |
      |  odr_income_pension_receiveOtherIncome_0 | yes |
      |  odr_income_pension_receiveOtherIncomeDetails | roi-details |

  @odr
  Scenario: income and benefits / damages
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-finances, finance-income-benefits"
    # empty
    When I press "odr_income_damage_save"
    Then the following fields should have an error:
      | odr_income_damage_expectCompensationDamages_0 |
      | odr_income_damage_expectCompensationDamages_1 |
    #  error
    Then I fill in the following:
      |  odr_income_damage_expectCompensationDamages_0 | yes |
      |  odr_income_damage_expectCompensationDamagesDetails |  |
    And I press "odr_income_damage_save"
    Then the following fields should have an error:
      | odr_income_damage_expectCompensationDamagesDetails |
    # correct
    When I fill in the following:
      |  odr_income_damage_expectCompensationDamages_0 | yes |
      |  odr_income_damage_expectCompensationDamagesDetails | ecd-details |
    And I press "odr_income_damage_save"
    Then the form should be valid
    # check saved ok
    When I click on "finance-income-benefits"
    Then the following fields should have the corresponding values:
      |  odr_income_damage_expectCompensationDamages_0 | yes |
      |  odr_income_damage_expectCompensationDamagesDetails | ecd-details |

  @odr
  Scenario: income and benefits / one off
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-finances, finance-income-benefits"
    And I save the page as "odr-income-benefits-empty"
    # empty
    And I press "odr_income_one_off_save"
    Then the form should be valid
    # correct
    When I check "odr_income_one_off_oneOff_0_present"
    And I check "odr_income_one_off_oneOff_2_present"
    And I press "odr_income_one_off_save"
    # check saved ok
    When I click on "finance-income-benefits"
    Then the following fields should have the corresponding values:
      |  odr_income_one_off_oneOff_0_present | 1 |
      |  odr_income_one_off_oneOff_2_present | 1 |