Feature: deputy / report / decisions

    @deputy
    Scenario: add decision
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-decisions"
        And I save the page as "report-decision-empty"
        # form errors
        When I follow "add-decisions-button"
        When I press "decision_save"
        And I save the page as "report-decision-add-error"
        Then the following fields should have an error:
            | decision_description |
            | decision_clientInvolvedDetails |
            | decision_clientInvolvedBoolean_0 |
            | decision_clientInvolvedBoolean_1 |
        # missing involvement details
        And I fill in the following:
            | decision_description | 2 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails |  |
        And I press "decision_save"
        And the form should be invalid
        # add decision
        And I click on "reports,report-2016-open, edit-decisions"
        And I add the following decisions:
            | description | clientInvolved | clientInvolvedDetails |
            | 2 beds | yes | the client was able to decide at 90% |
            | 3 beds | yes | the client was able to decide at 85% |
       And I should see "2 beds" in the "list-decisions" region
       And I should see "3 beds" in the "list-decisions" region
       And I save the page as "report-decision-list"