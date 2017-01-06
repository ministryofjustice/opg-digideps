Feature: odr / finance income benefits

  @odr
  Scenario: income and benefits
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-income_benefits, start"
    # State benefits: tick Housing benefit, Universal Credit
    And the step with the following values CANNOT be submitted:
      | income_benefits_stateBenefits_11_present     | 1 |       |
      | income_benefits_stateBenefits_11_moreDetails |   | [ERR] |
    And the step with the following values CAN be submitted:
      | income_benefits_stateBenefits_4_present      | 1      |
      | income_benefits_stateBenefits_11_present     | 1      |
      | income_benefits_stateBenefits_11_moreDetails | st11md |
    # Pensions and other income
    And the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | income_benefits_receiveStatePension_0 | yes |
    And the step with the following values CANNOT be submitted:
      | income_benefits_receiveOtherIncome_0      | yes |       |
      | income_benefits_receiveOtherIncomeDetails |     | [ERR] |
    And the step with the following values CAN be submitted:
      | income_benefits_receiveOtherIncome_0      | yes  |
      | income_benefits_receiveOtherIncomeDetails | roid |
    # damages
    And the step with the following values CANNOT be submitted:
      | income_benefits_expectCompensationDamages_0      | yes |       |
      | income_benefits_expectCompensationDamagesDetails |     | [ERR] |
    And the step with the following values CAN be submitted:
      | income_benefits_expectCompensationDamages_0      | yes  |
      | income_benefits_expectCompensationDamagesDetails | ecdd |
    # one off : tick Refunds, Sale of property
    And the step with the following values CAN be submitted:
      | income_benefits_oneOff_2_present | 1 |
      | income_benefits_oneOff_5_present | 1 |
        # check record in summary page
    And each text should be present in the corresponding region:
      | Housing benefit  | benefits                                  |
      | Other benefits   | benefits                                  |
      | Yes              | benefits-other-more-details               |
      | Yes              | receive-state-pension                     |
      | Yes              | receive-other-regular-income              |
      | roid             | receive-other-regular-income-more-details |
      | Yes              | compensation-awards                       |
      | ecdd             | compensation-awards-more-details          |
      | Refunds          | one-off                                   |
      | Sale of property | one-off                                   |
     # edit and check back link
    When I click on "edit" in the "benefits-other-more-details" region
    When I click on "step-back"
      # edit
    When I click on "edit" in the "benefits-other-more-details" region
    And I uncheck "income_benefits_stateBenefits_11_present"
    And I submit the step
    # check edited
    Then I should not see the "benefits-other-more-details" region
    And each text should be present in the corresponding region:
      | Housing benefit  | benefits                                  |
