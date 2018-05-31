Feature: Terms of use

    @deputy
    Scenario: init data
        Given I load the application status from "report-submit-pre"

    @deputy
    Scenario: The footer provides a link to terms of use in the login page
        Given I go to "/logout"
        And I go to "/login"
        Then the "Terms of use" link, in the footer, url should contain "/terms"

    @deputy
    Scenario: The footer provides a link to the terms of use when logged in
        Given I am logged in as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then the "Terms of use" link, in the footer, url should contain "/terms"

    @deputy
    Scenario: The terms of use page contains a back link
        Given I go to "/logout"
        And I go to "/login"
        And I go to "/terms"
        Then the "Back" link url should contain "/login"
