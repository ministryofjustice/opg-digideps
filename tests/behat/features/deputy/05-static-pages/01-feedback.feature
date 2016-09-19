Feature:  provide feedback
    
    @deputy
    Scenario: Feedback can be accessed by users who are not logged in
        Given I am not logged in
        And I go to "/feedback"
        Then I should see "Your feedback"

    @deputy
    Scenario: I give feedback on all fields and it is emailed to OPG
        Given I am not logged in
        Given emails are sent from "deputy" area
        And I reset the email log
        And I am on "/feedback"
        And I fill in the following:
            | feedback_difficulty | I found it to be really easy |
            | feedback_ideas | I think it needs an iPhone app |
            | feedback_help_3 | No, I filled in this form myself |
            | feedback_satisfactionLevel_1 | Satisfied |
        And I press "feedback_save"
        Then the form should be valid
        And I should see a "#feedback-thankyou" element
        And the last email should have been sent to "behat-digideps+feedback@digital.justice.gov.uk"
        And the last email should contain "I found it to be really easy"
        And the last email should contain "I think it needs an iPhone app"
        And the last email should contain "Satisfied"
        And the last email should contain "No, I filled in this form myself"

    @deputy
    Scenario: I give feedback on all fields including email and it is emailed to OPG
        Given I am not logged in
        And emails are sent from "deputy" area
        And I reset the email log
        # wrong email
        When I am on "/feedback"
        And I fill in the following:
            | feedback_emailYesNo_0 | yes |
            | feedback_email | wron |
        And I press "feedback_save"
        Then the following fields should have an error:
            |feedback_email |
        When I am on "/feedback"
        # empty email
        When I am on "/feedback"
        And I fill in the following:
            | feedback_difficulty | I found it to be really easy |
            | feedback_ideas | I think it needs an iPhone app |
            | feedback_help_3 | No, I filled in this form myself |
            | feedback_satisfactionLevel_1 | Satisfied |
            | feedback_emailYesNo_0 | no |
            | feedback_email | behat-feedback-sender-custom@publicguardian.gsi.gov.uk |
        And I press "feedback_save"
        Then the form should be valid
        And I should see a "#feedback-thankyou" element
        And the last email should have been sent to "behat-digideps+feedback@digital.justice.gov.uk"
        And the last email should not contain "behat-feedback-sender-custom@publicguardian.gsi.gov.uk"
        # add email
        When I am on "/feedback"
        And I fill in the following:
            | feedback_difficulty | I found it to be really easy |
            | feedback_ideas | I think it needs an iPhone app |
            | feedback_help_3 | No, I filled in this form myself |
            | feedback_satisfactionLevel_1 | Satisfied |
            | feedback_emailYesNo_0 | yes |
            | feedback_email | behat-feedback-sender-custom@publicguardian.gsi.gov.uk |
        And I press "feedback_save"
        Then the form should be valid
        And I should see a "#feedback-thankyou" element
        And the last email should have been sent to "behat-digideps+feedback@digital.justice.gov.uk"
        And the last email should contain "behat-feedback-sender-custom@publicguardian.gsi.gov.uk"


    @deputy
    Scenario: When I give feedback I dont have to fill all the fields in
        Given I am not logged in
        And emails are sent from "deputy" area
        And I reset the email log
        And I am on "/feedback"
        And I fill in the following:
            | feedback_help_3 | No, I filled in this form myself |
        And I press "feedback_save"
        Then the form should be valid
        And I should see a "#feedback-thankyou" element
        And the last email should have been sent to "behat-digideps+feedback@digital.justice.gov.uk"
        And the last email should contain "No, I filled in this form myself"
    
    @deputy
    Scenario: After giving feedback I see a thank you
        Given I am on "/feedback"
        And I fill in the following:
            | feedback_difficulty | I found it to be really easy |
        And I press "feedback_save"
        Then I should see "Thank you for sending your feedback"

    @deputy
    Scenario: Feedback email filled with logged users' email
        Given I am not logged in
        And emails are sent from "admin" area
        And I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
        When I create a new "ODR-disabled" "Lay Deputy" user "Feedback" "Sender" with email "behat-feedback-sender@publicguardian.gsi.gov.uk"
        And I activate the user with password "Abcd1234"
        #Given I am logged in as "behat-feedback-sender@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I go to "/feedback"
        Then I should see "Your feedback"
        And the "feedback_email" field should contain "behat-feedback-sender@publicguardian.gsi.gov.uk"

    @deputy
    Scenario: On the feedback screen I can go back to my previous page
        Given I am on the login page
        And I go to "/feedback"
        Then the "Back to deputy report" link url should contain "/"
            
    @deputy
    Scenario: On the thank you screen I see a link back to the client home
        Given I am on the login page
        And I go to "/feedback"
        And I fill in the following:
            | feedback_difficulty | I found it to be really easy |
        And I press "feedback_save"
        Then the form should be valid
        And I should see a "#feedback-thankyou" element
        And the "Return to deputy report" link url should contain "/"
