Feature: Prof deputy costs estimate

  Scenario: Status of section is reported on Report overview when section is not started
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    Then I should see a "#edit-prof_deputy_costs_estimate" element
    And I should see the "prof_deputy_costs_estimate-state-not-started" region

  Scenario: Completing the Fixed Costs route
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    And I click on "edit-prof_deputy_costs_estimate"
    Then I should be on "/report/1/prof-deputy-costs-estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | fixed |
    Then I should be on "/report/1/prof-deputy-costs-estimate/summary"
    And I should see "How will you be charging for your services?" in the "how-charged" region
    And I should see "Fixed costs" in the "how-charged" region

  Scenario: Status of section is reported on Report overview when section completed via Fixed Cost route
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000011" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | fixed |
    And I click on "breadcrumbs-report-overview"
    Then I should see the "prof_deputy_costs_estimate-state-done" region

  Scenario: Status of section is reported on Report overview when section partially completed for Assessed Costs route
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000012" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | assessed |
    And I click on "breadcrumbs-report-overview"
    Then I should see the "prof_deputy_costs_estimate-state-incomplete" region

  Scenario: Status of section is reported on Report overview when section partially completed for Both Costs route
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000013" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | both |
    And I click on "breadcrumbs-report-overview"
    Then I should see the "prof_deputy_costs_estimate-state-incomplete" region

  Scenario: Status of section is reported on Report overview when section completed via non Fixed Cost route
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000014" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | assessed |
    And I click on "save-and-continue"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHasMoreInfo_0 | no |
    And I click on "breadcrumbs-report-overview"
    Then I should see the "prof_deputy_costs_estimate-state-done" region

#  Scenario: Completing the Assessed Costs route
#  Scenario: Completing the Both Costs route
#  Scenario: Editing the How Charged page
#    # If keeping the existing choice - back to summary - otherwise delete all entries and continue normal route
#    # Only verify which of the two pages we arrive back at
#  Scenario: Editing the Cost Breakdown page
#  Scenario: Editing the More Information page
#  Scenario: Completing the Fixed Costs route then changing to complete the Assessed Costs route
#  Scenario: Completing the Fixed Costs route then changing to complete the Both Costs route
#  Scenario: Completing the Assessed Costs route then changing to complete the Fixed Costs route
#  Scenario: Completing the Assessed Costs route then changing to complete the Both Costs route
#  Scenario: Completing the Both Costs route then changing to complete the Fixed Costs route
#  Scenario: Completing the Both Costs route then changing to complete the Assessed Costs route
#  Scenario: Picking up where I left off from "Not Finished" status
#  Scenario: Jumping straight to summary from "Finished" status
