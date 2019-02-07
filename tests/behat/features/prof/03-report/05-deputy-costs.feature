Feature: PROF deputy costs

  Scenario: add cost fixed, no previous, no interim, other 2 items
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    And I click on "edit-prof_deputy_costs, start"
    # how charged: fixed only
    Then the step with the following values CAN be submitted:
      | deputy_costs_profDeputyCostsHowChargedFixed | 1 |
    # has previous: no
    And the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasPrevious_1 | no |
    # fixed cost
    And the step with the following values CAN be submitted:
      | deputy_costs_received_profDeputyFixedCost | 1000 |
    # SCCO
    And the step with the following values CAN be submitted:
      | deputy_costs_scco_profDeputyCostsAmountToScco         | 100         |
      | deputy_costs_scco_profDeputyCostsReasonBeyondEstimate | scco reason |
    # other costs breakdown
    And I click on "save-and-continue"
    #check summary
    And each text should be present in the corresponding region:
      | Fixed       | how-changed            |
      | No          | has-previous           |
      | 1,000.00    | fixed-cost-amount      |
      | 1,000       | total-cost             |
      | £100.00     | scco-assessment-amount |
      | scco reason | scco-assessment-reason |


  Scenario: all ticked, no previous, no interim, empty breakdown
    Given I load the application status from "pre-deputy-costs"
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    And I click on "edit-prof_deputy_costs, start"
    # how charged: all ticked
    Then the step with the following values CAN be submitted:
      | deputy_costs_profDeputyCostsHowChargedFixed    | 1 |
      | deputy_costs_profDeputyCostsHowChargedAssessed | 1 |
      | deputy_costs_profDeputyCostsHowChargedAgreed   | 1 |
    # previous = no
    And the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasPrevious_1 | no |
    # interim = no
    And the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasInterim_1 | no |
    # fixed
    And the step with the following values CAN be submitted:
      | deputy_costs_received_profDeputyFixedCost | 1000 |
    # scco
    And the step with the following values CAN be submitted:
      | deputy_costs_scco_profDeputyCostsAmountToScco | 100 |
    # other costs breakdown
    And I click on "save-and-continue"
    # check summary
    And each text should be present in the corresponding region:
      | Fixed    | how-changed       |
      | Assessed | how-changed       |
      | Agreed   | how-changed       |
      | No       | has-previous      |
      | No       | has-interim       |
      | 1,000.00 | fixed-cost-amount |

  Scenario: all ticked, previous, interim, 2 breakdown
    Given I load the application status from "pre-deputy-costs"
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    And I click on "edit-prof_deputy_costs, start"
    # how charged: all ticked
    Then the step with the following values CAN be submitted:
      | deputy_costs_profDeputyCostsHowChargedFixed    | 1 |
      | deputy_costs_profDeputyCostsHowChargedAssessed | 1 |
      | deputy_costs_profDeputyCostsHowChargedAgreed   | 1 |
    # previous=yes
    And the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasPrevious_1 | yes |
    And I fill in the following:
      | deputy_costs_previous_startDate_day   | 1    |
      | deputy_costs_previous_startDate_month | 1    |
      | deputy_costs_previous_startDate_year  | 2015 |
      | deputy_costs_previous_endDate_day     | 1    |
      | deputy_costs_previous_endDate_month   | 1    |
      | deputy_costs_previous_endDate_year    | 2016 |
      | deputy_costs_previous_amount          | 100  |
    And I click on "save-and-add-another"
    And I fill in the following:
      | deputy_costs_previous_startDate_day   | 1    |
      | deputy_costs_previous_startDate_month | 1    |
      | deputy_costs_previous_startDate_year  | 2015 |
      | deputy_costs_previous_endDate_day     | 2    |
      | deputy_costs_previous_endDate_month   | 1    |
      | deputy_costs_previous_endDate_year    | 2016 |
      | deputy_costs_previous_amount          | 200  |
    And I click on "save-and-continue"
    #interim = yes
    And the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasInterim_0 | yes |
    And the step with the following values CAN be submitted:
      | costs_interims_profDeputyInterimCosts_0_amount     | 50   |
      | costs_interims_profDeputyInterimCosts_0_date_day   | 1    |
      | costs_interims_profDeputyInterimCosts_0_date_month | 1    |
      | costs_interims_profDeputyInterimCosts_0_date_year  | 2015 |
      | costs_interims_profDeputyInterimCosts_1_amount     | 60   |
      | costs_interims_profDeputyInterimCosts_1_date_day   | 2    |
      | costs_interims_profDeputyInterimCosts_1_date_month | 1    |
      | costs_interims_profDeputyInterimCosts_1_date_year  | 2015 |
    # SCCO
    And the step with the following values CAN be submitted:
      | deputy_costs_scco_profDeputyCostsAmountToScco | 100 |
    # other costs breakdown: add two
    And the step with the following values CAN be submitted:
      | deputy_other_costs_profDeputyOtherCosts_0_amount      | 10                      |
      | deputy_other_costs_profDeputyOtherCosts_6_amount      | 55.5                    |
      | deputy_other_costs_profDeputyOtherCosts_6_moreDetails | breakdown other details |
    # check summary page
    And each text should be present in the corresponding region:
      | Fixed                                | how-changed             |
      | Assessed                             | how-changed             |
      | Agreed                               | how-changed             |
      | Yes                                  | has-previous            |
      | Received for 01/01/2015 - 01/01/2016 | prev-cost-1             |
      | £100                                 | prev-cost-1             |
      | £200                                 | prev-cost-2             |
      | Yes                                  | has-interim             |
      | £50.00, paid 01/01/2015              | interim-cost-1          |
      | £60.00, paid 02/01/2015              | interim-cost-2          |
      | £10.00                               | breakdown-appointments  |
      | £55.50                               | breakdown-other         |
      | breakdown other details              | breakdown-other-details |
      | £475.50                              | total-cost              |
    And I should not see the "fixed-cost-amount" region
