Feature: Privacy

    @deputy
    Scenario: The footer provides a link to privacy in the login page
        Given I go to "/logout"
        And I go to "/login"
        Then the "Privacy notice" link, in the footer, url should contain "/privacy"

    @deputy
    Scenario: The footer provides a link to the privacy page when logged in
        Given I am logged in as "laydeputy102@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then the "Privacy notice" link, in the footer, url should contain "/privacy"
        And I go to "/privacy"
        And the response status code should be 200

    @deputy
    Scenario: The privacy page contains a back link
        Given I go to "/logout"
        And I go to "/login"
        And the response status code should be 200
        Then I go to "/privacy"
        And the response status code should be 200
        And the "Back" link url should contain "/login"

