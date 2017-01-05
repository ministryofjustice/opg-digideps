Feature: deputy / report / visits and care

  @deputy
  Scenario: visits and care steps
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-visits_care, start"
      # step 1 empty
    And the step cannot be submitted without making a selection
      # step 1 missing details
    And the step with the following values CANNOT be submitted:
      | visits_care_doYouLiveWithClient_1      | no |       |
      | visits_care_howOftenDoYouContactClient |    | [ERR] |
      # step 1 correct
    And the step with the following values CAN be submitted:
      | visits_care_doYouLiveWithClient_1      | no    |
      | visits_care_howOftenDoYouContactClient | daily |
      # go back, check content, skip
    When I go back from the step
    Then the following fields should have the corresponding values:
      | visits_care_doYouLiveWithClient_1      | no    |
      | visits_care_howOftenDoYouContactClient | daily |
    Then I click on "step-skip"
      # step 2 empty
    And the step cannot be submitted without making a selection
      # step 2 missing details
    And the step with the following values CANNOT be submitted:
      | visits_care_doesClientReceivePaidCare_0 | yes |       |
      # step 2 correct
    And the step with the following values CAN be submitted:
      | visits_care_doesClientReceivePaidCare_0 | yes                 |
      | visits_care_howIsCareFunded_0           | client_pays_for_all |
      # go back, check content, skip
    When I go back from the step
    Then the following fields should have the corresponding values:
      | visits_care_doesClientReceivePaidCare_0 | yes                 |
      | visits_care_howIsCareFunded_0           | client_pays_for_all |
    Then I click on "step-skip"
      # step 3 empty
    And the step cannot be submitted without making a selection
      # step 3 correct
    And the step with the following values CAN be submitted:
      | visits_care_whoIsDoingTheCaring | the brother |
      # go back, check content, skip
    When I go back from the step
    Then the following fields should have the corresponding values:
      | visits_care_whoIsDoingTheCaring | the brother |
    Then I click on "step-skip"
      # step 4 empty
    And the step cannot be submitted without making a selection
      # step 4 missing details
    Then the step with the following values CANNOT be submitted:
      | visits_care_doesClientHaveACarePlan_0         | yes   | [ERR] |
      | visits_care_whenWasCarePlanLastReviewed_month |       | [ERR] |
      | visits_care_whenWasCarePlanLastReviewed_year  |       | [ERR] |
      # step 4 correct
    And the step with the following values CAN be submitted:
      | visits_care_doesClientHaveACarePlan_0         | yes  |
      | visits_care_whenWasCarePlanLastReviewed_month | 12   |
      | visits_care_whenWasCarePlanLastReviewed_year  | 2015 |

  @deputy
  Scenario: visits and care summary page and edit
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-visits_care"
      # Summary overview
    Then each text should be present in the corresponding region:
      | No                    | live-with-client              |
      | daily                 | how-often-contact-client      |
      | Yes                   | does-client-receive-paid-care |
      | pays for all the care | how-is-care-funded            |
      | the brother           | who-is-doing-caring           |
      | Yes                   | client-has-care-plan          |
      | December 2015         | care-plan-last-reviewed       |
      # edit and check back link
    When I click on "live-with-client-edit, step-back"
      # edit
    When I click on "live-with-client-edit"
    And the step with the following values CAN be submitted:
      | visits_care_doYouLiveWithClient_0 | yes |
      # check edited
    Then I should see "Yes" in the "live-with-client" region
    And I should not see the "how-often-contact-client" region
