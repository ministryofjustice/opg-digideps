Feature: deputy / user / set password

    @deputy
    Scenario: login and add user (deputy)
        When I open the activation page for "behat-user@publicguardian.gov.uk"
        Then the response status code should be 200
         # empty
        When I fill in the password fields with ""
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
        #password mismatch
        When I fill in the following:
            | set_password_password_first   | DigidepsPass1234 |
            | set_password_password_second  | DigidepsPass12345 |
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
        # not long enough
        When I fill in the password fields with "Digideps1234"
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
        # nolowercase
        When I fill in the password fields with "DIGIDEPSPASS1234"
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
        # nouppercase
        When I fill in the password fields with "digidepspass1234"
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
        # no number
        When I fill in the password fields with "DigidepsPassword"
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
        # not agreed on TC
        When I fill in the password fields with "DigidepsPass1234"
        And I uncheck "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
        # too common password
        When I fill in the password fields with "Password123"
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be invalid
        # correct !!
        When I fill in the password fields with "DigidepsPass1234"
        And I check "set_password_showTermsAndConditions"
        And I press "set_password_save"
        Then the form should be valid
        And I should be on "/login"
        And I should see "Sign in to your new account"
        When I fill in the following:
            | email     | behat-user@publicguardian.gov.uk |
            | password  | DigidepsPass1234 |
        And I press "login_login"
        Then I should not see an "#error-summary" element

    @ndr
    Scenario: login and add user (deputy ndr)
        When I activate the user "behat-user-ndr@publicguardian.gov.uk" with password "DigidepsPass1234"
        # test login
        And I go to "logout"
        And I go to "/login"
        And I fill in the following:
            | email     | behat-user-ndr@publicguardian.gov.uk |
            | password  | DigidepsPass1234 |
        And I press "login_login"
        Then I should not see an "#error-summary" element
