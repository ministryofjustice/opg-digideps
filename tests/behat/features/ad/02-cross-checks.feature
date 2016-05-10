Feature: assisted digital / acl


    Scenario: An Assisted Digital user cannot login into deputy area
        # check AD can login into admin site
        Given I am logged in to admin as "behat-ad-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #Then the response status code should be 200
        # check AD CANNOT login into DEPUTY site
        Given I go to "/logout"
        And  I am on the login page
        When I fill in the following:
            | login_email     | behat-ad-user@publicguardian.gsi.gov.uk |
            | login_password  | Abcd1234 |
        And I click on "login"
        Then I should see an "#error-summary" element
        And I should be on "/login"


    Scenario: An ad user cannot reset password from the deputy area
        # check admin can recover password from admin site
        Given I reset the email log
        And I am on admin login page
        When I click on "forgotten-password"
        And I fill in "password_forgotten_email" with "behat-ad-user@publicguardian.gsi.gov.uk"
        And I press "password_forgotten_submit"
        Then the last email should have been sent to "behat-ad-user@publicguardian.gsi.gov.uk"
        # check admin CANNOT recover password from DEPUTY site
        Given I reset the email log
        And I am on the login page
        When I click on "forgotten-password"
        And I fill in "password_forgotten_email" with "behat-ad-user@publicguardian.gsi.gov.uk"
        And I press "password_forgotten_submit"
        #Then the response status code should be 200
        And no email should have been sent
