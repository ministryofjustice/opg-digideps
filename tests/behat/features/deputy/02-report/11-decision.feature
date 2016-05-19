Feature:deputy / report / edit decision

    @deputy
    Scenario: edit decision, remove the decision
        Given I load the application status from "report-submit-pre"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports, report-2016"
        And I click on "edit-decisions, decisions"
        And the URL should match "/report/\d+/decisions"
        And I save the page as "editremdec-start"
        And I click on "decision-2-beds"
        Then the following fields should have the corresponding values:
            | decision_description | 2 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I click on "cancel-edit"
        And the URL should match "/report/\d+/decisions"
        And I click on "decision-2-beds"
        When I fill in the following:
            | decision_description |  |
            | decision_clientInvolvedDetails |  |
        And I press "decision_save"
        Then the following fields should have an error:
            | decision_description |
            | decision_clientInvolvedDetails |
        When I fill in the following:
            | decision_description | 5 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 100% |
        And I press "decision_save"
        Then I should see "5 beds" in the "list-decisions" region
        And I should see "the client was able to decide at 100%" in the "list-decisions" region
        And I click on "decision-5-beds"
        And I click on "delete-button"
        And the URL should match "/report/\d+/decisions"
        Then I should not see "the client was able to decide at 100%" in the "list-decisions" region
        Then I should not see the "5 beds" link
        And I should not see the "list-assets" region
        And I save the page as "editremdec-start"
        
    @deputy
    Scenario: add explanation for no decisions
      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
      And I click on "reports, report-2016"
      # delete current decision
      And I click on "edit-decisions, decisions"
      And I click on "decision-3-beds"
      And I click on "delete-button"
      And I save the page as "report-no-decision-empty"
      # add explanation
      Then the reason_for_no_decision_reasonForNoDecisions field is expandable
      # empty form throws error
      When I fill in "reason_for_no_decision_reasonForNoDecisions" with ""
      And I press "reason_for_no_decision_save"
      Then the form should be invalid
      And I save the page as "report-no-decision-error"
      # add reason
      When I fill in the following:
        | reason_for_no_decision_reasonForNoDecisions | small budget |
      And I press "reason_for_no_decision_save"
      Then the form should be valid
      And I should see "small budget" in the "reason-no-decisions" region
      And I save the page as "report-no-decision-added"
      # edit reason, and cancel
      When I click on "edit-reason-no-decisions"
      Then the following fields should have the corresponding values:
        | reason_for_no_decision_reasonForNoDecisions | small budget |
      When I click on "cancel-reason-button"
      Then the URL should match "/report/\d+/decisions"
      # edit reason, and save
      When I click on "edit-reason-no-decisions"
      And I save the page as "report-no-decision-edit"
      And I fill in the following:
        | reason_for_no_decision_reasonForNoDecisions ||
      And I press "reason_for_no_decision_save"
      Then the form should be invalid
      And I save the page as "report-no-decision-error"
      And I fill in the following:
        | reason_for_no_decision_reasonForNoDecisions | nothing relevant purchased or sold |
      And I press "reason_for_no_decision_save"
      And I save the page as "report-no-decision-edit"
      And I should see "nothing relevant purchased or sold" in the "reason-no-decisions" region
      # delete reason and cancel
      When I click on "edit-reason-no-decisions"
      When I click on "delete-button"
      Then the URL should match "/report/\d+/decisions"
      And the following fields should have the corresponding values:
        | reason_for_no_decision_reasonForNoDecisions | |

