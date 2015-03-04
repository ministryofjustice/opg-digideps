Feature: report
    
    @deputy
    Scenario: add decision
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I go to "/report/1/decisions/add"
        # missing title
        And I fill in the following:
            | decision_title |  |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 31 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2014 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
        And the form should contain an error
         # missing description
        And I fill in the following:
            | decision_title | Bought house in Sw18 |
            | decision_description |  |
            | decision_decisionDate_day | 31 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2014 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
        And the form should contain an error
        # missing date
        And I fill in the following:
            | decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day |  |
            | decision_decisionDate_month |  |
            | decision_decisionDate_year |  |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
        And the form should contain an error
        # wrong date
        And I fill in the following:
            | decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 31 |
            | decision_decisionDate_month | 02 |
            | decision_decisionDate_year | 9999 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
        And the form should contain an error
         # missing involvement details
        And I fill in the following:
            | decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 31 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2014 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails |  |
        And I submit the form
        And the form should contain an error
        # right values
        And I fill in the following:
            | decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 31 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2014 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
        Then the response status code should be 200
        And the form should not contain an error
        And I should be on "/report/1/decisions"
        And I should see "Bought house in Sw18" in the "decision" region
