Feature: Generic feedback page

    @deputy
    Scenario: The phase banner provides a link to the feedback page
        Given I am on "/"
        When I follow "feedback"
        Then I should be on "/feedback"

    @deputy
    Scenario: Feedback page accepts as little as a comment
        Given I am on "/feedback"
        And emails are sent from "deputy" area
        When I fill in "feedback_report_comments" with "Test comment"
        And I press "Save"
        Then the response should contain "Feedback sent"
        And the last email should have been sent to "digideps+feedback@digital.justice.gov.uk"
        And the last email should contain "Test comment"

    @deputy
    Scenario: Extra details are included in the email
        Given I am on "/feedback"
        And emails are sent from "deputy" area
        When I fill in "feedback_report_satisfactionLevel_4" with "1"
        And I fill in "feedback_report_comments" with "Test comment"
        And I fill in "feedback_report_name" with "My name"
        And I fill in "feedback_report_email" with "myemail@emailhost.com"
        And I press "Save"
        Then the last email should have been sent to "digideps+feedback@digital.justice.gov.uk"
        And the last email should contain "Very dissatisfied"
        And the last email should contain "Test comment"
        And the last email should contain "My name"
        And the last email should contain "myemail@emailhost.com"
