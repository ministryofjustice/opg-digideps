Feature:  provide feedback
    
    @feedback @deputy
    Scenario: Feedback can be accessed by users who are not logged in
        Given I am not logged in
        And I goto the feedback page
        Then I should see "Your feedback"

    @feedback @deputy
    Scenario: I give feedback on all fields and it is emailed to OPG
        Given I reset the email log
        And I am on the feedback page
        And I fill in the following:
            | feedback_difficulty | I found it to be really easy |
            | feedback_ideas | I think it needs an iPhone app |
            | feedback_help_3 | No, I filled in this form myself |
            | feedback_satisfactionLevel_1 | Satisfied |
        And I press "feedback_save"
        Then the form should be valid
        And the last email should have been sent to "behat-deputyshipfeedback@publicguardian.gsi.gov.uk"
        And the last email should contain "I found it to be really easy"
        And the last email should contain "I think it needs an iPhone app"
        And the last email should contain "Satisfied"
        And the last email should contain "No, I filled in this form myself"
        
    @feedback @deputy
    Scenario: When I give feedback I dont have to fill all the fields in 
        Given I reset the email log
        And I am on the feedback page
        And I fill in the following:
            | feedback_help_3 | No, I filled in this form myself |
        And I press "feedback_save"
        Then the form should be valid
        And the last email should have been sent to "behat-deputyshipfeedback@publicguardian.gsi.gov.uk"
        And the last email should contain "No, I filled in this form myself"
    
    @feedback @deputy
    Scenario: After giving feedback I see a thank you
        Given I am on the feedback page
        And I fill in the following:
            | feedback_difficulty | I found it to be really easy |
        And I press "feedback_save"
        Then I should see "Thank you for sending your feedback"
        
    @feedback @deputy
    Scenario: On the feedback screen I can go back to my previous page
        Given I am on the login page
        And I goto the feedback page
        Then the "Back to deputy report" link url should contain "/login"
            
    @feedback @deputy
    Scenario: On the thank you screen I see a link back to the client home
        Given I am on the login page
        And I goto the feedback page
        And I fill in the following:
            | feedback_difficulty | I found it to be really easy |
        And I press "feedback_save"
        Then the form should be valid
        And the "Back to deputy report" link url should contain "/client"
