Feature: Report actions

  @deputy
  Scenario: report actions
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-actions, start"
      # step 1
    And the step cannot be submitted without making a selection
    Then the step with the following values CANNOT be submitted:
      | action_doYouExpectFinancialDecisions_0      | yes |       |
      | action_doYouExpectFinancialDecisionsDetails |     | [ERR] |
    Then the step with the following values CAN be submitted:
      | action_doYouExpectFinancialDecisions_0      | yes    |
      | action_doYouExpectFinancialDecisionsDetails | dyefdd |
    # step 2
    And the step cannot be submitted without making a selection
    Then the step with the following values CANNOT be submitted:
      | action_doYouHaveConcerns_0      | yes |       |
      | action_doYouHaveConcernsDetails |     | [ERR] |
    Then the step with the following values CAN be submitted:
      | action_doYouHaveConcerns_0      | yes   |
      | action_doYouHaveConcernsDetails | dyhcd |
    # check summary page
    And each text should be present in the corresponding region:
      | Yes    | expect-financial-decision         |
      | dyefdd | expect-financial-decision-details |
      | Yes    | have-concerns                     |
      | dyhcd  | have-concerns-details             |
    # check step 1 reloaded
    When I click on "edit" in the "expect-financial-decision" region
    Then the following fields should have the corresponding values:
      | action_doYouExpectFinancialDecisions_0      | yes    |
      | action_doYouExpectFinancialDecisionsDetails | dyefdd |
    And I go back from the step
    # check step 2 reloaded
    When I click on "edit" in the "have-concerns" region
    Then the following fields should have the corresponding values:
      | action_doYouHaveConcerns_0      | yes   |
      | action_doYouHaveConcernsDetails | dyhcd |
    And I go back from the step