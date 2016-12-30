Feature: deputy / report / decisions

    @deputy
    Scenario: add decision
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-decisions, start"
        # step 1
        Then the step cannot be submitted without making a selection
        When I fill in the step with the following, save and go back checking it's saved:
            | mental_capacity_hasCapacityChanged_1 | stayedSame |
        Then the step with the following values CANNOT be submitted:
            | mental_capacity_hasCapacityChanged_0 | changed |
        And the step with the following values CAN be submitted:
            | mental_capacity_hasCapacityChanged_0 | changed |
            | mental_capacity_hasCapacityChangedDetails | mchccd |
        # exists: no + explanation
        Given the step cannot be submitted without making a selection
        Then the step with the following values CANNOT be submitted:
            | decision_exist_hasDecisions_1 | no |
        And the step with the following values CAN be submitted:
            | decision_exist_hasDecisions_1 | no |
            | decision_exist_reasonForNoDecisions | rfnd |
        # summary page check
        And each text should be present in the corresponding region:
            | Changed | mental-capacity     |
            | mchccd  | mental-capacity-changed-details     |
            | No      | has-decisions       |
            | rfnd    | reason-no-decisions |
        # exist: yes
        #Given I click on "edit" in the "has-decisions" region