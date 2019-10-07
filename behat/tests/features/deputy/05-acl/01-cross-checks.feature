Feature: deputy / acl / cross domain (admin and deputy) checks

    @deputy
    Scenario: A deputy cannot login into admin area
        # check deputy can login into deputy site
        Given I load the application status from "report-submit-pre"
        And I am logged in as "behat-lay-deputy-ndr@publicguardian.gov.uk" with password "Abcd1234"
        #Then the response status code should be 200
        # check deputy CANNOT login into ADMIN site
        Given I go to "/logout"
        And I am on admin login page
        When I fill in the following:
            | login_email     | behat-lay-deputy-ndr@publicguardian.gov.ukk |
            | login_password  | Abcd1234 |
        And I click on "login"
        Then I should see an "#error-summary" element
        And I should be on "/login"


    @deputy
    Scenario: A deputy cannot reset password from the admin area
        # check deputy can recover password from deputy site
        Given emails are sent from "deputy" area
        And I go to "/login"
        When I click on "forgotten-password"
        And I fill in "password_forgotten_email" with "behat-lay-deputy-102@publicguardian.gov.uk"
        And I press "password_forgotten_submit"
        Then the last email should have been sent to "behat-lay-deputy-102@publicguardian.gov.uk"
