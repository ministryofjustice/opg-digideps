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

    @lay-pfa-high-not-started-multi-client-deputy @login-breadcrumbs-client-details
    Scenario: A user logs in with their primary account and uses breadcrumbs to navigate to Client details page
        Given a Lay Deputy tries to login with their "primary" email address
        And I am associated with '1' 'pfa' court order(s)
        When the Lay deputy navigates to client details page
        And they choose their "primary" Client
        Then they should be on the "primary" Client's dashboard

    @lay-pfa-low-completed @login-redirect
    Scenario: A user accesses a protected page, is redirected to login, then is sent back to their original page (DDLS-1188)
        Given a Lay Deputy has completed a Pfa Low Assets report
        And I am associated with '1' 'pfa' court order(s)
        And I log out
        And I visit the page of a court order that 'I am' associated with
        Then I should be on client login page
        When I log in
        Then I should be on the court order page
