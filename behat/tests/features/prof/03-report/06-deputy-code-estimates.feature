@pd-cost-estimate
Feature: Prof deputy costs estimate

  # Happy paths and Overview status checks
  Scenario: Status of section is reported on Report overview when section is not started
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000010" region
    Then I should see a "#edit-prof_deputy_costs_estimate" element
    And I should see the "prof_deputy_costs_estimate-state-not-started" region

  Scenario: Completing the Fixed Costs route and viewing the status overview
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000010" region
    And I click on "edit-prof_deputy_costs_estimate"
    Then the URL should match "/report/\d+/prof-deputy-costs-estimate"
    When I click on "start"
    And I submit the step
    Then the following fields should have an error:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 |
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_1 |
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_2 |
    And I should see an "#error-summary" element
    Then the URL should match "/report/\d+/prof-deputy-costs-estimate/how-charged"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | fixed |
    Then the URL should match "/report/\d+/prof-deputy-costs-estimate/summary"
    And I should see "How will you be charging for your services?" in the "how-charged" region
    And I should see "Fixed costs" in the "how-charged" region
    And I should not see "General management costs"
    And I should not see "Contact with the client, their family and friends"
    And I should not see "Contact with case managers and care providers"
    And I should not see "Contact with other parties"
    And I should not see "Work on forms and other documents"
    And I should not see "Total estimated costs"
    And I should not see "More information"
    And I should not see "More information details"
    When I click on "breadcrumbs-report-overview"
    Then I should see the "prof_deputy_costs_estimate-state-done" region

  Scenario: Partially completed Assessed Costs route
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000011" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | assessed |
    And I click on "breadcrumbs-report-overview"
    Then I should see the "prof_deputy_costs_estimate-state-incomplete" region

  Scenario: Partially completed Both Costs route
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000012" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | both |
    And I click on "breadcrumbs-report-overview"
    Then I should see the "prof_deputy_costs_estimate-state-incomplete" region

  Scenario: Completing the Assessed Costs route with only management cost and viewing the status overview
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000013" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | assessed |
    And the URL should match "/report/\d+/prof-deputy-costs-estimate/breakdown"
    And the step with the following values CAN be submitted:
      | deputy_estimate_costs_profDeputyManagementCostAmount       | 4.99  |
    And the URL should match "/report/\d+/prof-deputy-costs-estimate/more-info"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHasMoreInfo_0 | no    |
    And the URL should match "/report/\d+/prof-deputy-costs-estimate/summary"
    And I should see "How will you be charging for your services?" in the "how-charged" region
    And I should see "Assessed costs" in the "how-charged" region
    And I should see "4.99" in the "management-cost" region
    And I should see "Contact with the client, their family and friends" in the "breakdown-contact-client" region
    And I should see "£0.00" in the "breakdown-contact-client" region
    And I should see "Contact with case managers and care providers" in the "breakdown-contact-case-manager-carers" region
    And I should see "£0.00" in the "breakdown-contact-case-manager-carers" region
    And I should see "Contact with other parties" in the "breakdown-contact-others" region
    And I should see "£0.00" in the "breakdown-contact-others" region
    And I should see "Work on forms and other documents" in the "breakdown-forms-documents" region
    And I should see "£0.00" in the "breakdown-forms-documents" region
    And I should see "No more information to add" in the "more-info" region
    When I click on "breadcrumbs-report-overview"
    Then I should see the "prof_deputy_costs_estimate-state-done" region

  Scenario: Completing the Assessed Costs route with costs and more info and viewing the status overview
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000014" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | assessed |
    And the URL should match "/report/\d+/prof-deputy-costs-estimate/breakdown"
    And the step with the following values CAN be submitted:
      | deputy_estimate_costs_profDeputyManagementCostAmount        | 150   |
      | deputy_estimate_costs_profDeputyEstimateCosts_0_amount      | 10.01 |
      | deputy_estimate_costs_profDeputyEstimateCosts_1_amount      | 20.02 |
      | deputy_estimate_costs_profDeputyEstimateCosts_2_amount      | 30.03 |
      | deputy_estimate_costs_profDeputyEstimateCosts_3_amount      | 40.04 |
    And the URL should match "/report/\d+/prof-deputy-costs-estimate/more-info"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHasMoreInfo_0   | yes        |
      | deputy_costs_estimate_profDeputyCostsEstimateMoreInfoDetails | Extra text |
    And the URL should match "/report/\d+/prof-deputy-costs-estimate/summary"
    And I should see "How will you be charging for your services?" in the "how-charged" region
    And I should see "Assessed costs" in the "how-charged" region
    And I should see "150.00" in the "management-cost" region
    And I should see "Contact with the client, their family and friends" in the "breakdown-contact-client" region
    And I should see "£10.01" in the "breakdown-contact-client" region
    And I should see "Contact with case managers and care providers" in the "breakdown-contact-case-manager-carers" region
    And I should see "£20.02" in the "breakdown-contact-case-manager-carers" region
    And I should see "Contact with other parties" in the "breakdown-contact-others" region
    And I should see "£30.03" in the "breakdown-contact-others" region
    And I should see "Work on forms and other documents" in the "breakdown-forms-documents" region
    And I should see "£40.04" in the "breakdown-forms-documents" region
    And I should see "Extra text" in the "more-info" region
    When I click on "breadcrumbs-report-overview"
    Then I should see the "prof_deputy_costs_estimate-state-done" region

  Scenario: Selecting the Both Costs route directs towards Assessed Costs route
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000015" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | both |
    And the URL should match "/report/\d+/prof-deputy-costs-estimate/breakdown"

  # Editing non Fixed Costs route answers
  Scenario: Editing the answers for the Assessed Cost route
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000016" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | assessed |
    And the step with the following values CAN be submitted:
      | deputy_estimate_costs_profDeputyManagementCostAmount       | 4.99  |
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHasMoreInfo_0   | yes        |
      | deputy_costs_estimate_profDeputyCostsEstimateMoreInfoDetails | Extra text |
    And the URL should match "/report/\d+/prof-deputy-costs-estimate/summary"
    When I click on "edit-more-info"
    Then the URL should match "/report/\d+/prof-deputy-costs-estimate/more-info"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHasMoreInfo_0   | yes    |
      | deputy_costs_estimate_profDeputyCostsEstimateMoreInfoDetails | Edited text |
    And the URL should match "/report/\d+/prof-deputy-costs-estimate/summary"
    And I should see "Answer edited"
    And I should see "Edited text" in the "more-info" region
    And I should not see "Extra text" in the "more-info" region
    When I click on "edit-more-info"
    Then the URL should match "/report/\d+/prof-deputy-costs-estimate/more-info"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHasMoreInfo_0   | no |
    And the URL should match "/report/\d+/prof-deputy-costs-estimate/summary"
    And I should see "Answer edited"
    And I should see "No more information to add" in the "more-info" region
    And I should not see "Edited text"

  # Switching between Fixed Costs and non Fixed Costs
  Scenario: Switching from Fixed Costs selection to non Fixed Costs selection
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000017" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | fixed |
    When I click on "edit-how-charged"
    Then the URL should match "/report/\d+/prof-deputy-costs-estimate/how-charged"
    When the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | assessed |
    Then the URL should match "/report/\d+/prof-deputy-costs-estimate/breakdown"
    When the step with the following values CAN be submitted:
      | deputy_estimate_costs_profDeputyManagementCostAmount   | 30.99 |
      | deputy_estimate_costs_profDeputyEstimateCosts_2_amount | 30.03 |
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHasMoreInfo_0   | yes        |
      | deputy_costs_estimate_profDeputyCostsEstimateMoreInfoDetails | Extra text |
    Then the URL should match "/report/\d+/prof-deputy-costs-estimate/summary"
    And I should see "Assessed costs" in the "how-charged" region
    And I should see "30.99" in the "management-cost" region
    And I should see "Contact with the client, their family and friends" in the "breakdown-contact-client" region
    And I should see "£0.00" in the "breakdown-contact-client" region
    And I should see "Contact with case managers and care providers" in the "breakdown-contact-case-manager-carers" region
    And I should see "£0.00" in the "breakdown-contact-case-manager-carers" region
    And I should see "Contact with other parties" in the "breakdown-contact-others" region
    And I should see "£30.03" in the "breakdown-contact-others" region
    And I should see "Work on forms and other documents" in the "breakdown-forms-documents" region
    And I should see "£0.00" in the "breakdown-forms-documents" region
    And I should see "Extra text" in the "more-info" region

  Scenario: Switching from non Fixed Costs selection to Fixed Costs selection
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000018" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | assessed |
    And the step with the following values CAN be submitted:
      | deputy_estimate_costs_profDeputyManagementCostAmount   | 30.90 |
      | deputy_estimate_costs_profDeputyEstimateCosts_2_amount | 30.03 |
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHasMoreInfo_0 | no |
    When I click on "edit-how-charged"
    Then the URL should match "/report/\d+/prof-deputy-costs-estimate/how-charged"
    When the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | fixed |
    Then the URL should match "/report/\d+/prof-deputy-costs-estimate/summary"
    And I should see "Answer edited"
    And I should see "How will you be charging for your services?" in the "how-charged" region
    And I should see "Fixed costs" in the "how-charged" region
    And I should not see "General management costs"
    And I should not see "Contact with the client, their family and friends"
    And I should not see "Contact with case managers and care providers"
    And I should not see "Contact with other parties"
    And I should not see "Work on forms and other documents"
    And I should not see "Total estimated costs"
    And I should not see "More information"
    And I should not see "More information details"

  # Entering a completed section
  Scenario: Entering a completed Fixed Cost route takes me to summary
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000019" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | fixed |
    When I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs_estimate"
    Then the URL should match "/report/\d+/prof-deputy-costs-estimate/summary"

  Scenario: Entering a completed Assessed Cost route takes me to summary
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000020" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | assessed |
    And the step with the following values CAN be submitted:
      | deputy_estimate_costs_profDeputyManagementCostAmount       | 4.99  |
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHasMoreInfo_0 | no |
    When I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs_estimate"
    Then the URL should match "/report/\d+/prof-deputy-costs-estimate/summary"

  # Entering a partially completed section
  Scenario: Entering a partially completed non Fixed Costs route up to breakdown takes me to breakdown page
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000021" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | assessed |
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs_estimate"
    And the URL should match "/report/\d+/prof-deputy-costs-estimate/breakdown"

  Scenario: Entering a partially completed non Fixed Costs route up to more info takes me to more info page
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000022" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0 | assessed |
    And the step with the following values CAN be submitted:
      | deputy_estimate_costs_profDeputyManagementCostAmount       | 4.99  |
    And I click on "breadcrumbs-report-overview"
    And I click on "edit-prof_deputy_costs_estimate"
    And the URL should match "/report/\d+/prof-deputy-costs-estimate/more-info"

  # Form validation
  Scenario: Submitting form with missing data
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-31000023" region
    And I click on "edit-prof_deputy_costs_estimate"
    When I click on "start"
    Then the step cannot be submitted without making a selection
    When the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHowCharged_0   | assessed |
    And the step with the following values CANNOT be submitted:
      | deputy_estimate_costs_profDeputyManagementCostAmount        |          |
    And the step with the following values CAN be submitted:
      | deputy_estimate_costs_profDeputyManagementCostAmount        | 1001.01  |
    Then the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHasMoreInfo_0   | yes     |
      | deputy_costs_estimate_profDeputyCostsEstimateMoreInfoDetails |         |
    And the step with the following values CAN be submitted:
      | deputy_costs_estimate_profDeputyCostsEstimateHasMoreInfo_0   | no      |
