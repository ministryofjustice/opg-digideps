Feature: PROF deputy costs

  Scenario: add cost fixed, no previous, no interim
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    And I click on "edit-prof_deputy_costs, start"
    Then the step with the following values CAN be submitted:
      | deputy_costs_profDeputyCostsHowChargedFixed | 1 |
    And the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasPrevious_1 | no |
    And the step with the following values CAN be submitted:
      | deputy_costs_received_profDeputyFixedCost | 1000 |
    And the step with the following values CAN be submitted:
      | deputy_costs_scco_profDeputyCostsAmountToScco         | 100         |
      | deputy_costs_scco_profDeputyCostsReasonBeyondEstimate | scco reason |
    And the step with the following values CAN be submitted:
      | deputy_other_costs_profDeputyOtherCosts_0_amount      | 10                      |
      | deputy_other_costs_profDeputyOtherCosts_6_amount      | 55.5                    |
      | deputy_other_costs_profDeputyOtherCosts_6_moreDetails | breakdown other details |
    And each text should be present in the corresponding region:
      | Fixed                   | how-changed             |
      | No                      | has-previous            |
      | 1,000.00                | fixed-cost-amount       |
      | 1,000                   | total-cost              |
      | £100.00                 | scco-assessment-amount  |
      | scco reason             | scco-assessment-reason  |
      | £10.00                  | breakdown-appointments  |
      | £55.50                  | breakdown-other         |
      | breakdown other details | breakdown-other-details |
