Feature: deputy / user / set password
    
    @deputy
    Scenario: login and add user (deputy)
        Given emails are sent from "deputy" area
        Given I am on "/logout"
        # follow link
        When I save the application status into "activation-link-before-opening"
        When I open the "/user/activate/" link from the email
        Then the response status code should be 200
         # empty
        When I fill in the password fields with ""
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
        #password mismatch
        When I fill in the following: 
            | set_password_password_first   | Abcd1234 |
            | set_password_password_second  | Abcd12345 |
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
        # nolowercase
        When I fill in the password fields with "ABCD1234"
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
        # nouppercase
        When I fill in the password fields with "abcd1234"
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
        # no number
        When I fill in the password fields with "Abcdefgh"
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
         # not agreed on TC
        When I fill in the password fields with "Abcd1234"
        And I uncheck "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
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

    @ndr
    Scenario: login and add user (deputy ndr)
        Given emails are sent from "admin" area
        Given I am on "/logout"
       # follow link as it is
        When I open the "/user/activate/" link from the email
        Then the response status code should be 200
        And I activate the user with password "Abcd1234"
       # test login
        When I go to "logout"
        And I go to "/login"
        And I fill in the following:
            | login_email     | behat-user-ndr@publicguardian.gsi.gov.uk |
            | login_password  | Abcd1234 |
        And I press "login_login"
        Then I should not see an "#error-summary" element
    
