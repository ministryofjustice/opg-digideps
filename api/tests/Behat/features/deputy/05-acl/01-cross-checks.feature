Feature: deputy / acl / cross domain (admin and deputy) checks

    @deputy
    Scenario: A deputy cannot login into admin area
        # check deputy can login into deputy site
        Given I load the application status from "report-submit-pre"
        And I am logged in as "behat-lay-deputy-ndr@publicguardian.gov.uk" with password "DigidepsPass1234"
        #Then the response status code should be 200
        # check deputy CANNOT login into ADMIN site
        Given I go to "/logout"
        And I am on admin login page
        When I fill in the following:
            | email     | behat-lay-deputy-ndr@publicguardian.gov.ukk |
            | password  | DigidepsPass1234 |
        And I click on "login"
        Then I should see an "#error-summary" element
        And I should be on "/login"


    @deputy
    Scenario: A deputy cannot reset password from the admin area
        # check deputy can recover password from deputy site
        When I go to "/login"
        And I click on "forgotten-password"
        And I fill in "password_forgotten_email" with "behat-lay-deputy-102@publicguardian.gov.uk"
        And I press "password_forgotten_submit"
