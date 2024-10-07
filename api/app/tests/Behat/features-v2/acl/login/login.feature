@v2 @v2_admin @login
Feature: Users logging into the service

    @super-admin
    Scenario: A super admin attempts to login with the wrong password multiple times and gets locked out
        Given a super admin user tries to login with an invalid password
        Then I should see "You've entered an invalid email or password. Please try again."
        Given a super admin user tries to login with an invalid password
        Then I should see "You've entered an invalid email or password. Please try again."
        Given a super admin user tries to login with an invalid password
        Then I should see "You've entered an invalid email or password. Please try again."
        Given a super admin user tries to login with an invalid password
        Then I should see "You've entered an invalid email or password. Please try again."
        Given a super admin user tries to login with an invalid password
        Then I should see "You've entered an invalid email or password. Please try again."
        Given a super admin user tries to login with an invalid password
        Then I should see "You've entered an invalid email or password. Please try again."
        Given a super admin user tries to login with an invalid password
        Then I should see "You've entered an invalid email or password. Please try again."
        Given a super admin user tries to login with an invalid password
        Then I should see "You've entered an invalid email or password. Please try again."
        Given a super admin user tries to login with an invalid password
        Then I should see "You've entered an invalid email or password. Please try again."
        Given a super admin user tries to login with an invalid password
        Then I should see "You've tried to sign in with the wrong password too many times. Please check your password, wait for 30 minutes, and try again."

    @lay-pfa-high-not-started-legacy-password-hash
    Scenario: A user logins to the service and their password hash is upgraded, and updated their password
        Given a Lay Deputy exists with a legacy password hash
        When the user I'm interacting with logs in to the frontend of the app
        Then their password hash should automatically be upgraded
        Given I view the lay deputy change password page
        When I fill in the following:
            | change_password_current_password | DigidepsPass1234 |
            | change_password_password_first | DigidepsPass12345 |
            | change_password_password_second | DigidepsPass12345 |
        And I press "change_password_save"
        Then the form should be valid


    @multi-feature-flag-enabled
    Scenario: A user tries to login to the service with their non primary account
        Given a Lay Deputy has multiple client accounts
        When a Lay Deputy tries to login with their "non-primary" email address
        Then they get redirected back to the log in page
        And a flash message should be displayed to the user with their primary email address
        When the user tries to access their clients report overview page
        Then they get redirected back to the log in page

    @multi-feature-flag-enabled
    Scenario: A user tries to login to the service with their primary account
        Given a Lay Deputy has multiple client accounts
        And a Lay Deputy tries to login with their "primary" email address
        Then they should be on the Choose a Client homepage
        And when they log out they shouldn't see a flash message for non primary accounts
