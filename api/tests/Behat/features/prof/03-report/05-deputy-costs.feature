Feature: PROF deputy costs

  Scenario: add cost fixed, no previous, no interim, other 2 items
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I fill in "search" with "31000010"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-31000010" region
    And I click on "edit-prof_deputy_costs, start"
    And I submit the step
    Then the following fields should have an error:
      | deputy_costs_profDeputyCostsHowCharged_0 |
      | deputy_costs_profDeputyCostsHowCharged_1 |
      | deputy_costs_profDeputyCostsHowCharged_2 |
    And I should see an "#error-summary" element
    # how charged: fixed only
    Then the step with the following values CAN be submitted:
      | deputy_costs_profDeputyCostsHowCharged_0 | fixed |
    # has previous: no
    And the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasPrevious_1 | no |
    # fixed cost
    And the step with the following values CAN be submitted:
      | deputy_costs_received_profDeputyFixedCost | 1000 |
    # other costs breakdown
    Then the step with the following values CANNOT be submitted:
      | deputy_other_costs_profDeputyOtherCosts_6_amount      | 30.03 |
      | deputy_other_costs_profDeputyOtherCosts_6_moreDetails |       |
    When the step with the following values CAN be submitted:
      | deputy_other_costs_profDeputyOtherCosts_6_amount      | 30.03 |
      | deputy_other_costs_profDeputyOtherCosts_6_moreDetails | info  |
    #check summary
    Then each text should be present in the corresponding region:
      | Fixed costs | how-charged            |
      | No          | has-previous           |
      | 1,000.00    | fixed-cost-amount      |
      | 1,030.03    | total-cost-taken-from-client |

  Scenario: all ticked, no previous, no interim, empty breakdown
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I fill in "search" with "3138393T"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-3138393T" region
    And I click on "edit-prof_deputy_costs, start"
    # how charged: all ticked
    Then the step with the following values CAN be submitted:
      | deputy_costs_profDeputyCostsHowCharged_2    | both |
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
    And I save the current URL as "breakdown-page"
    And I click on "breadcrumbs-report-overview"
    And I should see the "prof_deputy_costs-state-incomplete" region
    And I go to the URL previously saved as "breakdown-page"
    # other costs breakdown
    And I click on "save-and-continue"
    # check summary
    And each text should be present in the corresponding region:
      | Both fixed and assessed costs     | how-charged       |
      | No       | has-previous      |
      | No       | has-interim       |
      | 1,000.00 | fixed-cost-amount |
      | £100.00     | scco-assessment-amount |
    And I click on "breadcrumbs-report-overview"
    And I should see the "prof_deputy_costs-state-done" region

  # Entering the section at the correct subsection
  Scenario: Entering partially completed sections with Fixed costs
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I fill in "search" with "31498120"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-31498120" region
    And I click on "edit-prof_deputy_costs, start"
    And the step with the following values CAN be submitted:
      | deputy_costs_profDeputyCostsHowCharged_0 | fixed |
    When I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/previous-received-exists"
    When the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasPrevious_1 | no |
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/costs-received"
    When the step with the following values CAN be submitted:
      | deputy_costs_received_profDeputyFixedCost | 1000 |
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/breakdown"
    # submit empty (none) breakdown costs
    And I click on "save-and-continue"
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/summary"

  Scenario: Entering partially completed sections with non Fixed costs and has interim
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I fill in "search" with "32000002"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-32000002" region
    And I click on "edit-prof_deputy_costs, start"
    And the step with the following values CAN be submitted:
      | deputy_costs_profDeputyCostsHowCharged_1 | assessed |
    When I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/previous-received-exists"
    When the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasPrevious_1 | no |
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/interim-exists"
    When the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasInterim_0 | yes |
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/interim"
    When the step with the following values CAN be submitted:
      | costs_interims_profDeputyInterimCosts_0_amount     | 50   |
      | costs_interims_profDeputyInterimCosts_0_date_day   | 1    |
      | costs_interims_profDeputyInterimCosts_0_date_month | 1    |
      | costs_interims_profDeputyInterimCosts_0_date_year  | 2015 |
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/amount-scco"
    When the step with the following values CAN be submitted:
      | deputy_costs_scco_profDeputyCostsAmountToScco | 100 |
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/breakdown"
    # submit empty (none) breakdown costs
    And I click on "save-and-continue"
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/summary"

  Scenario: Entering partially completed sections with non Fixed costs and not interim
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I fill in "search" with "32000003"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-32000003" region
    And I click on "edit-prof_deputy_costs, start"
    And the step with the following values CAN be submitted:
      | deputy_costs_profDeputyCostsHowCharged_1 | assessed |
    When I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/previous-received-exists"
    When the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasPrevious_1 | no |
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/interim-exists"
    When the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasInterim_0 | no |
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/costs-received"
    When the step with the following values CAN be submitted:
      | deputy_costs_received_profDeputyFixedCost | 1000 |
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/amount-scco"
    When the step with the following values CAN be submitted:
      | deputy_costs_scco_profDeputyCostsAmountToScco | 100 |
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/breakdown"
    # submit empty (none) breakdown costs
    And I click on "save-and-continue"
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs"
    Then the url should match "/report/\d+/prof-deputy-costs/summary"

  Scenario: all ticked, previous, interim, 2 breakdown
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I fill in "search" with "33000002"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-33000002" region
    And I click on "edit-prof_deputy_costs, start"
    # how charged: all ticked
    Then the step with the following values CAN be submitted:
      | deputy_costs_profDeputyCostsHowCharged_2    | both |
    # previous=yes
    And the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasPrevious_1 | yes |
    And the step with the following values CANNOT be submitted:
      | deputy_costs_previous_startDate_day   | 2    |
      | deputy_costs_previous_startDate_month | 1    |
      | deputy_costs_previous_startDate_year  | 2015 |
      | deputy_costs_previous_endDate_day     | 1    |
      | deputy_costs_previous_endDate_month   | 1    |
      | deputy_costs_previous_endDate_year    | 2015 |
      | deputy_costs_previous_amount          | 100  |
    And I fill in the following:
      | deputy_costs_previous_startDate_day   | 1    |
      | deputy_costs_previous_startDate_month | 1    |
      | deputy_costs_previous_startDate_year  | 2015 |
      | deputy_costs_previous_endDate_day     | 1    |
      | deputy_costs_previous_endDate_month   | 1    |
      | deputy_costs_previous_endDate_year    | 2015 |
      | deputy_costs_previous_amount          | 100  |
    And I click on "save-and-add-another"
    And the step with the following values CANNOT be submitted:
      | deputy_costs_previous_startDate_day   | 1    |
      | deputy_costs_previous_startDate_month | 1    |
      | deputy_costs_previous_startDate_year  | 2015 |
      | deputy_costs_previous_endDate_day     | 2    |
      | deputy_costs_previous_endDate_month   | 1    |
      | deputy_costs_previous_endDate_year    | 2016 |
      | deputy_costs_previous_amount          | 100  |
    And I fill in the following:
      | deputy_costs_previous_startDate_day   | 1    |
      | deputy_costs_previous_startDate_month | 1    |
      | deputy_costs_previous_startDate_year  | 2015 |
      | deputy_costs_previous_endDate_day     | 1    |
      | deputy_costs_previous_endDate_month   | 1    |
      | deputy_costs_previous_endDate_year    | 2016 |
      | deputy_costs_previous_amount          | 200  |
    And I click on "save-and-continue"
    #interim = yes
    And the step with the following values CAN be submitted:
      | yes_no_profDeputyCostsHasInterim_0 | yes |
    And the step with the following values CANNOT be submitted:
      | costs_interims_profDeputyInterimCosts_0_amount     | 50   |
      | costs_interims_profDeputyInterimCosts_0_date_day   | 1    |
      | costs_interims_profDeputyInterimCosts_0_date_month | 1    |
      | costs_interims_profDeputyInterimCosts_0_date_year  | 2100 |
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
      | Both fixed and assessed costs                | how-charged             |
      | Yes                                          | has-previous            |
      | Received for 1 January 2015 - 1 January 2015 | prev-cost-1             |
      | £100                                         | prev-cost-1             |
      | £200                                         | prev-cost-2             |
      | Yes                                          | has-interim             |
      | £50.00, paid 1 January 2015                  | interim-cost-1          |
      | £60.00, paid 2 January 2015                  | interim-cost-2          |
      | £10.00                                       | breakdown-appointments  |
      | £55.50                                       | breakdown-other         |
      | breakdown other details                      | breakdown-other-details |
      | £475.50                                      | total-cost-taken-from-client |
    And I should not see the "fixed-cost-amount" region
