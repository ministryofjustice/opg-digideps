Feature: set password
    
    @deputy
    Scenario: login and add user (deputy)
        Given I am on "/logout"
        When I open the first link on the email
        Then the response status code should be 200
        And I save the page as "deputy-step1"
        And the "set_password_email" field should contain "behat-user@publicguardian.gsi.gov.uk"
         # empty
        When I fill in the following: 
            | set_password_password_first   |  |
            | set_password_password_second  |  |
        And I press "set_password_save"
        Then the form should contain an error
        #password mismatch
        When I fill in the following: 
            | set_password_password_first   | Abcd1234 |
            | set_password_password_second  | Abcd12345 |
        And I press "set_password_save"
        Then the form should contain an error
        # nolowercase
        When I fill in the following: 
            | set_password_password_first   | ABCD1234 |
            | set_password_password_second  | ABCD1234 |
        And I press "set_password_save"
        Then the form should contain an error
        # nouppercase
        When I fill in the following: 
            | set_password_password_first   | abcd1234 |
            | set_password_password_second  | abcd1234 |
        And I press "set_password_save"
        Then the form should contain an error
        # no number
        When I fill in the following: 
            | set_password_password_first   | Abcdefgh |
            | set_password_password_second  | Abcdefgh |
        And I press "set_password_save"
        Then the form should contain an error
        And I save the page as "deputy-step1-error"
        # correct !!
        When I fill in the following: 
            | set_password_password_first   | Abcd1234 |
            | set_password_password_second  | Abcd1234 |
        And I press "set_password_save"
        Then the form should not contain an error
        And I should see the "user-details" region
        # test login
        When I go to "logout"
        And I go to "/login"
        And I fill in the following: 
            | login_email     | behat-user@publicguardian.gsi.gov.uk |
            | login_password  | Abcd1234 |
        And I press "login_login"
        Then I should not see the "header errors" region

   
    @admin
    Scenario: login and add user (admin)
        Given I am on "/logout"
        When I open the first link on the email
        Then the response status code should be 200
        And I save the page as "admin-step1"
        And the "set_password_email" field should contain "behat-admin-user@publicguardian.gsi.gov.uk"
        # only testing the correct case, as the form is the same for deputy
        When I fill in the following: 
            | set_password_password_first   | Abcd1234 |
            | set_password_password_second  | Abcd1234 |
        And I press "set_password_save"
        Then I should not see the "header errors" region
        And I should be on "/admin/"