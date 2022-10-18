@v2 @v2_admin @login
Feature: Users are not able to login with an invalid password, and the app prevents brute force attempts.

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
