@v2 @v2_admin @admin-client-discharge
Feature: Admin - Client Discharge

    @super-admin @lay-pfa-high-submitted
    Scenario: A super admin user discharges a client
        Given a super admin user accesses the admin app
        When I visit the admin client details page for an existing client linked to a Lay deputy
        And I attempt to discharge the client
        Then the client should be discharged

    @admin-manager @lay-pfa-high-submitted
    Scenario: An admin manager user can discharge a client
        Given an admin manager user accesses the admin app
        When I visit the admin client details page for an existing client linked to a Lay deputy
        And I attempt to discharge the client
        Then the client should be discharged

    @admin @lay-pfa-high-submitted
    Scenario: An admin user cannot discharge a client
        Given an admin user accesses the admin app
        When I visit the admin client details page for an existing client linked to a Lay deputy
        And I attempt to discharge the client
        Then the client should not be discharged

    @multi-feature-flag-enabled @lay-pfa-high-not-started-multi-client-deputy-discharged-client
    Scenario: A user tries to login to the service with their primary account and access non-primary discharged client
        And a Lay Deputy tries to login with their "primary" email address
        Then they should be on the Choose a Client homepage
        When they try to access their "non-primary" discharged Client
        Then I should be redirected and denied access to continue as client not found
