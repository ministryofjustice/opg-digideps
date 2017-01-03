Feature: deputy / report / decisions

    @deputy
    Scenario: decisions
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-decisions, start"
        # step  mental capacity
        Then the step cannot be submitted without making a selection
        When I fill in the step with the following, save and go back checking it's saved:
            | mental_capacity_hasCapacityChanged_1 | stayedSame |
        Then the step with the following values CANNOT be submitted:
            | mental_capacity_hasCapacityChanged_0 | changed |
        And the step with the following values CAN be submitted:
            | mental_capacity_hasCapacityChanged_0 | changed |
            | mental_capacity_hasCapacityChangedDetails | mchccd |
        # chose "no records"
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
        # select there are records (from summary page link)
        Given I click on "edit" in the "has-decisions" region
        And the step with the following values CAN be submitted:
            | decision_exist_hasDecisions_1 | yes |
        # add decision n.1 (and validate form)
        And the step cannot be submitted without making a selection
        And the step with the following values CANNOT be submitted:
            | decision_description |  |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails |  |
        And the step with the following values CAN be submitted:
            | decision_description | dd1 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | dcid1 |
        # add decision n.2
        And I choose "yes" when asked for adding another record
        And the step with the following values CAN be submitted:
            | decision_description | dd2 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | dcid2 |
        # add another: no
        And I choose "no" when asked for adding another record
        # check record in summary page
        And each text should be present in the corresponding region:
            | dd1   | decision-1 |
            | Yes | decision-1 |
            | dcid1 | decision-1 |
            | dd2   | decision-2 |
            | Yes | decision-2 |
            | dcid2 | decision-2 |
        # remove decision n.2
        When I click on "delete" in the "decision-2" region
        Then I should not see the "decision-2" region
        # test add link
        When I click on "add"
        Then I should see the "save-and-continue" link
        When I click on "step-back"
        # edit decision n.1
        When I click on "edit" in the "decision-1" region
        Then the following fields should have the corresponding values:
            | decision_description | dd1 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | dcid1 |
        And the step with the following values CAN be submitted:
            | decision_description | dd1-changed |
            | decision_clientInvolvedBoolean_1 | 0 |
            | decision_clientInvolvedDetails | dcid1-changed |
        And each text should be present in the corresponding region:
            | dd1-changed   | decision-1 |
            | No | decision-1 |
            | dcid1-changed | decision-1 |
