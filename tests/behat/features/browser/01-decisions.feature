Feature: Browser - manage decisions
    
    @browser
    Scenario: browser - Add and delete reason for no decisions
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "edit-decisions, decisions"
        When I fill in the following:
            | reason_for_no_decision_reasonForNoDecisions | small budget |
        And I save the page as "decision-reason"
        And I press "reason_for_no_decision_save"
        Then I should see "small budget" in the "reason-no-decisions" region
        When I click on "edit-reason-no-decisions, delete-button"
        And I save the page as "decision-reason-delete-confirm"
        Then I should see a confirmation
        When I click on "delete-confirm"
        Then the URL should match "/report/\d+/decisions"
        And the following fields should have the corresponding values:
            | reason_for_no_decision_reasonForNoDecisions | |
        
    @browser
    Scenario: browser - Add two decisions then delete one
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "edit-decisions, decisions"
        When I follow "add-decisions-button"
        Then I save the page as "decision-add"
        And I click on "reports, report-start, edit-decisions"
        And I add the following decisions:
            | description | clientInvolved | clientInvolvedDetails |
            | 2 beds | yes | the client was able to decide at 90% |
            | 3 beds | yes | the client was able to decide at 85% |
        And I save the page as "decision-list"
        Then I should see "2 beds" in the "list-decisions" region
        And I should see "3 beds" in the "list-decisions" region
        Then I press "edit-1-link"
        And I click on "delete-button"
        And I save the page as "decision-delete-confirm"
        Then I should see a confirmation
        When I click on "delete-confirm"
        Then the URL should match "/report/\d+/decisions"
        And I should not see "2 beds" in the "list-decisions" region
        And I should see "3 beds" in the "list-decisions" region
        
