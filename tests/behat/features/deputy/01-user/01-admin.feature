Feature: deputy / user / add user

    @deputy
    Scenario: login and add deputy user
        Given I reset the email log
        Given I am on admin login page
        And I save the page as "admin-login"
        Then the response status code should be 200
        # test wrong credentials
        When I fill in the following:
            | login_email     | admin@publicguardian.gsi.gov.uk |
            | login_password  |  WRONG PASSWORD !! |
        And I click on "login"
        Then I should see an "#error-summary" element
        And I save the page as "admin-login-error1"
        # test user email in caps
        When I fill in the following:
            | login_email     | ADMIN@PUBLICGUARDIAN.GSI.GOV.UK |
            | login_password  | Abcd1234 |
        And I click on "login"
        Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
        Given I am not logged into admin
        # test right credentials
        When I fill in the following:
            | login_email     | admin@publicguardian.gsi.gov.uk |
            | login_password  | Abcd1234 |
        And I click on "login"
        #When I go to "/admin"
        Given I am on admin page "/admin"
        # invalid email
        When I fill in the following:
            | admin_email | invalidEmail |
            | admin_firstname | 1 |
            | admin_lastname | 2 |
            | admin_roleId | 2 |
        And I press "admin_save"
        Then the form should be invalid
        And I save the page as "admin-deputy-error1"
        And I should not see "invalidEmail" in the "users" region
        # assert form OK
        When I create a new "Lay Deputy" user "John" "Doe" with email "behat-user@publicguardian.gsi.gov.uk"
        Then I should see "behat-user@publicguardian.gsi.gov.uk" in the "users" region
        Then I should see "Lay Deputy" in the "users" region
        And I save the page as "admin-deputy-added"
        And the last email containing a link matching "/user/activate/" should have been sent to "behat-user@publicguardian.gsi.gov.uk"

   
