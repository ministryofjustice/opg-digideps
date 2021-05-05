Feature: deputy / Activation link resending
    When the token expires, the user can have the activation link resent and continue from the user activation step

    @deputy
    Scenario: Activation link: resend expired link and restart from activation step
        Given I load the application status from "report-submit-pre"
        And I change the user "behat-user@publicguardian.gov.uk" token to "behatuser123abc" dated last week
        When I go to "/user/activate/behatuser123abc"
        And I click on "ask-us-to-send-new-link"
        Then I should be on "/user/activate/password/sent/behatuser123abc"
        And the response status code should be 200

    @deputy
    Scenario: Forgotten password page: expired token shows error page and link to go back
        Given I change the user "behat-user@publicguardian.gov.uk" token to "behatuser123abc" dated last week
        When I go to "/user/password-reset/behatuser123abc"
        And I click on "go-back-to-reset-password"
        Then I should be on "/password-managing/forgotten"
