@gt
Feature: Users can reset their password via self-service

    Scenario: Deputy can reset password
        Given I am on "/login"
        When I follow "Forgotten your password?"
        And I fill in "password_forgotten_email" with "behat-lay-deputy-102-4@publicguardian.gov.uk"
        And I press "Reset your password"
        Then the form should be valid
        When I open the password reset page for "behat-lay-deputy-102-4@publicguardian.gov.uk"
        And I fill in the following:
            | reset_password_password_first  | Abcd12345 |
            | reset_password_password_second | Abcd12345 |
        And I press "Save password"
        And I go to "/login"
        And I fill in the following:
            | login_email     | behat-lay-deputy-102-4@publicguardian.gov.uk |
            | login_password  | Abcd12345                                    |
        And I press "Sign in"
        Then the form should be valid

    Scenario: Admin can reset password
        Given I am on "/login"
        When I follow "Forgotten your password?"
        And I fill in "password_forgotten_email" with "case-manager@publicguardian.gov.uk"
        And I press "Reset your password"
        Then the form should be valid
        When I open the password reset page for "case-manager@publicguardian.gov.uk"
        And I fill in the following:
            | reset_password_password_first  | Abcd12345 |
            | reset_password_password_second | Abcd12345 |
        And I press "Save password"
        And I go to "/login"
        And I fill in the following:
            | login_email     | case-manager@publicguardian.gov.uk |
            | login_password  | Abcd12345                          |
        And I press "Sign in"
        Then the form should be valid

    Scenario: Invalid emails are not accepted
        Given I am on "/login"
        When I follow "Forgotten your password?"
        And I fill in "password_forgotten_email" with "invalidemail"
        And I press "Reset your password"
        Then the form should be invalid

    Scenario: Non-existent emails are accepted
        Given I am on "/login"
        When I follow "Forgotten your password?"
        And I fill in "password_forgotten_email" with "incorrectemail@publicguardian.gov.uk"
        And I press "Reset your password"
        Then the form should be valid

    # Scenario: Password reset
    #   Given emails are sent from "deputy" area
    #   And I go to "/logout"
    #   And I go to "/login"
    #   When I click on "forgotten-password"
    #   # empty form
    #   And I fill in "password_forgotten_email" with ""
    #   And I press "password_forgotten_submit"
    #   Then the form should be invalid
    #   # invalid email
    #   When I fill in "password_forgotten_email" with "invalidemail"
    #   And I press "password_forgotten_submit"
    #   Then the form should be invalid
    #   # non-existing email (no email is sent)
    #   When I fill in "password_forgotten_email" with "ehat-not-existing@publicguardian.gov.uk"
    #   And I press "password_forgotten_submit"
    #   Then the form should be valid
    #   And I click on "return-to-login"
    #   And no "deputy" email should have been sent to "ehat-not-existing@publicguardian.gov.uk"
    #   # existing email (email is now sent)
    #   When I go to "/login"
    #   And I click on "forgotten-password"
    #   And I fill in "password_forgotten_email" with "BEHAT-UsEr@publicguardian.gov.uk"
    #   And I press "password_forgotten_submit"
    #   Then the form should be valid
    #   # open password reset page
    #   When I open the password reset page for "behat-user@publicguardian.gov.uk"
    #   # empty
    #   When I fill in the following:
    #       | reset_password_password_first   |  |
    #       | reset_password_password_second  |  |
    #   And I press "reset_password_save"
    #   Then the form should be invalid
    #   #password mismatch
    #   When I fill in the following:
    #       | reset_password_password_first   | Abcd1234 |
    #       | reset_password_password_second  | Abcd12345 |
    #   And I press "reset_password_save"
    #   Then the form should be invalid
    #   # (nolowercase, nouppercase, no number skipped as already tested in "set password" scenario)
    #   # correct !!
    #   When I fill in the following:
    #       | reset_password_password_first   | Abcd12345 |
    #       | reset_password_password_second  | Abcd12345 |
    #   And I press "reset_password_save"
    #   Then the form should be valid
    #   And the URL should match "/lay"
    #   # test login
    #   Given I am logged in as "behat-user@publicguardian.gov.uk" with password "Abcd12345"
    #   Then the URL should match "/lay"
    #   # assert set password link is not accessible
    #   When I open the "/user/password-reset/" link from the email
    #   Then the response status code should be 500
    #   # restore previous password
    #   And I load the application status from "reset-password-start"
