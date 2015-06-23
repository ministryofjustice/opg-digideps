Feature: Formatted Report
    
    @formatted-report
    Scenario: A report lists decisions with and without client involvement
        Given the laydeputy@digital user is signed up
        And I login as laydeputy
        Then I add a dummy contact
        And I add a dummy bank account
        And I add a dummy asset
        And I add a decision "blah blah" with client involvement saying "blah blah"
        And I add a decision "blah blah" with client involvement saying "blah blah"
        And I add a decision "blah blah" with no client involvement
        And I submit the report
        Then I view the formatted report
        Then the formatted report contains 3 decisions
    
    @formatted-report    
    Scenario: A report lists decisions with and without client involvement
        Given the laydeputy@digital user is signed up
        And I login as laydeputy
        Then I add a dummy contact
        And I add a dummy bank account
        And I add a dummy asset
        And give a reason for no decision as "small budget"  
        And I submit the report
        Then I view the formatted report
        Then the formatted report contains 3 decisions 