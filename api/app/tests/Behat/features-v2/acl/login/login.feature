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


    @lay-pfa-high-not-started-multi-client-deputy
    Scenario: A user tries to login to the service with their non primary account
        Given a Lay Deputy tries to login with their "non-primary" email address
        Then they get redirected back to the log in page
        And a flash message should be displayed to the user with their primary email address
        When the user tries to access their clients report overview page
        Then they get redirected back to the log in page

    @lay-pfa-high-not-started-multi-client-deputy
    Scenario: A user tries to login to the service with their primary account
        And a Lay Deputy tries to login with their "primary" email address
        Then they should be on the Choose a Client homepage
        When they choose their "primary" Client
        Then they should be on the "primary" Client's dashboard
        When the Lay deputy navigates back to the Choose a Client homepage
        When they choose their "non-primary" Client
        Then they should be on the "non-primary" Client's dashboard
        And when they log out they shouldn't see a flash message for non primary accounts

    @lay-pfa-high-not-started-multi-client-deputy-with-ndr
    Scenario: A user tries to login to the service with their primary account and has an NDR
        And a Lay Deputy tries to login with their "primary" email address
        Then they should be on the Choose a Client homepage
        When they choose their "primary" Client
        Then they should be on the "primary" Client's dashboard
        When the Lay deputy navigates back to the Choose a Client homepage
        When they choose their "non-primary" Client
        Then they should be on the "non-primary" Client's dashboard
        And when they log out they shouldn't see a flash message for non primary accounts

    @lay-pfa-high-not-started-multi-client-deputy
    Scenario: A user logs in with their primary account and uses breadcrumbs to navigate client dashboard
        And a Lay Deputy tries to login with their "primary" email address
        When they choose their "primary" Client
        Then they should be on the "primary" Client's dashboard
        And the Lay deputy navigates back to the Choose a Client homepage using the breadcrumb

    @lay-pfa-high-not-started-multi-client-deputy
    Scenario: A user logs in with their primary account and uses breadcrumbs to navigate report overview page
        And a Lay Deputy tries to login with their "primary" email address
        When they choose their "primary" Client
        Then they should be on the "primary" Client's dashboard
        When the Lay deputy navigates to the report overview page
        And the Lay Deputy navigates back to the Client dashboard using the breadcrumb
        Then they should be on the "primary" Client's dashboard
        When the Lay deputy navigates to the report overview page
        And the Lay deputy navigates back to the Choose a Client homepage using the breadcrumb

    @lay-pfa-high-not-started-multi-client-deputy
    Scenario: A user logs in with their primary account and uses breadcrumbs to navigate Your details page
        And a Lay Deputy tries to login with their "primary" email address
        When the Lay deputy navigates to your details page
        And the Lay deputy navigates back to the Choose a Client homepage using the breadcrumb

    @lay-pfa-high-not-started-multi-client-deputy
    Scenario: A user logs in with their primary account and uses breadcrumbs to navigate Client details page
        And a Lay Deputy tries to login with their "primary" email address
        When they choose their "primary" Client
        Then they should be on the "primary" Client's dashboard
        When the Lay deputy navigates to client details page
        And the Lay deputy navigates back to the Choose a Client homepage using the breadcrumb
        When they choose their "primary" Client
        Then they should be on the "primary" Client's dashboard
        When the Lay deputy navigates to client details page
        And the Lay Deputy navigates back to the Client dashboard using the breadcrumb
        Then they should be on the "primary" Client's dashboard

    @lay-pfa-high-started-multi-client-deputy-primary-client-discharged-two-active-clients
    Scenario: A user logs into the service with their primary account given they're active clients are linked to their secondary accounts
        Given a Lay Deputy tries to login with their "primary" email address
        Then they should be on the Choose a Client homepage
        And have access to all active client dashboards
        When they try to access their "primary" discharged Client
        Then I should be redirected and denied access to continue as client not found

    @super-admin @lay-pfa-high-started-multi-client-deputy-primary-client-discharged-two-active-clients
    Scenario: A user logs into the service with their primary account given they're remaining active client is linked to their secondary account
        Given a super admin user accesses the admin app
        And they discharge the deputy from "1" secondary client(s)
        Then a Lay Deputy tries to login with their "primary" email address
        And should arrive on the client dashboard of their only active client

    @super-admin @lay-pfa-high-started-multi-client-deputy-primary-client-discharged-two-active-clients
    Scenario: A user logs into the service with their primary account given all of their clients are discharged
        Given a super admin user accesses the admin app
        And they discharge the deputy from "2" secondary client(s)
        Then a Lay Deputy tries to login with their "primary" email address
        Then they should be on the add your client page
