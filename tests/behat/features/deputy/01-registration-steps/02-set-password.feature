Feature: deputy / user / set password
    
    @deputy
    Scenario: login and add user (deputy)
        Given emails are sent from "admin" area
        Given I am on "/logout"
        # assert email link doesn't work on admin area
        When I open the "/user/activate/" link from the email on the "admin" area
        Then the response status code should be 500
        # follow link as it is
        When I save the application status into "activation-link-before-opening"
        When I open the "/user/activate/" link from the email
        #Then the response status code should be 200
        And I save the page as "deputy-step1"
         # empty
        When I fill in the following: 
            | set_password_password_first   |  |
            | set_password_password_second  |  |
        And I press "set_password_save"
        Then the form should be invalid
        #password mismatch
        When I fill in the following: 
            | set_password_password_first   | Abcd1234 |
            | set_password_password_second  | Abcd12345 |
        And I press "set_password_save"
        Then the form should be invalid
        # nolowercase
        When I fill in the following: 
            | set_password_password_first   | ABCD1234 |
            | set_password_password_second  | ABCD1234 |
        And I press "set_password_save"
        Then the form should be invalid
        # nouppercase
        When I fill in the following: 
            | set_password_password_first   | abcd1234 |
            | set_password_password_second  | abcd1234 |
        And I press "set_password_save"
        Then the form should be invalid
        # no number
        When I fill in the following: 
            | set_password_password_first   | Abcdefgh |
            | set_password_password_second  | Abcdefgh |
        And I press "set_password_save"
        Then the form should be invalid
        And I save the page as "deputy-step1-error"
        # correct !!
        When I load the application status from "activation-link-before-opening"
        And I activate the user with password "Abcd1234"
        Then the form should be valid
        And I should see the "user-details" region
        # test login
        When I go to "logout"
        And I go to "/login"
        And I fill in the following: 
            | login_email     | behat-user@publicguardian.gsi.gov.uk |
            | login_password  | Abcd1234 |
        And I press "login_login"
        Then I should not see an "#error-summary" element

    @odr
    Scenario: login and add user (deputy odr)
        Given emails are sent from "admin" area
        Given I am on "/logout"
       # follow link as it is
        When I open the "/user/activate/" link from the email
       #Then the response status code should be 200
        And I save the page as "odr-deputy-step1"
        And I activate the user with password "Abcd1234"
       # test login
        When I go to "logout"
        And I go to "/login"
        And I fill in the following:
            | login_email     | behat-user-odr@publicguardian.gsi.gov.uk |
            | login_password  | Abcd1234 |
        And I press "login_login"
        Then I should not see an "#error-summary" element
    
