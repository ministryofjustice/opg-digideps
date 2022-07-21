@v2 @v2_admin @admin-deputy-management
Feature: Admin - Edit deputy users

    @super-admin @lay-health-welfare-not-started
    Scenario: A super admin user edits a deputy users details
        Given a super admin user accesses the admin app
        And a Lay Deputy exists
        Then I should be able to update the deputies firstname, lastname, postcode and email address
        When I update the details of the deputy available to me
        Then the deputies details should be updated

    @admin-manager @lay-health-welfare-not-started
    Scenario: An admin manager user edits a deputy users details
        Given an admin manager user accesses the admin app
        And a Lay Deputy exists
        Then I should be able to update the deputies firstname, lastname, postcode and email address
        When I update the details of the deputy available to me
        Then the deputies details should be updated

    @admin @lay-health-welfare-not-started
    Scenario: An admin user edits a deputy users details
        Given an admin user accesses the admin app
        And a Lay Deputy exists
        Then I should be able to update the deputies firstname, lastname and postcode
        And I should not be able to update the deputies email address
        When I update the details of the deputy available to me
        Then the deputies details should be updated

    @super-admin @admin-manager @admin
    Scenario: A super admin user updates other admin users details
        Given a super admin user accesses the admin app
        And another super admin user exists
        When I attempt to update an existing "super admin" users details
        Then the users details should be updated
        When I attempt to update an existing "admin manager" users details
        Then the users details should be updated
        When I attempt to update an existing admin users details
        Then the users details should be updated

    @super-admin @admin-manager @admin
    Scenario: An admin manager user updates other admin users details
        Given an admin manager user accesses the admin app
        And another admin manager user exists
        When I attempt to update an existing "admin manager" users details
        Then the users details should not be updated
        When I attempt to update an existing "super admin" users details
        Then the users details should not be updated
        When I attempt to update an existing "admin" users details
        Then the users details should be updated

    @super-admin @admin-manager @admin
    Scenario: An admin user updates other admin users details
        Given an admin user accesses the admin app
        And another admin user exists
        When I attempt to update an existing "admin" users details
        Then the users details should be updated
        When I attempt to update an existing "super admin" users details
        Then the users details should not be updated
        When I attempt to update an existing "admin manager" users details
        Then the users details should not be updated

    @super-admin @lay-health-welfare-not-started
    Scenario: A super admin user enters invalid data
        Given a super admin user accesses the admin app
        And a Lay Deputy exists
        When I visit the admin Edit User page for the user I'm interacting with
        And I enter empty values
        Then I should see 'missing values' errors
        When I enter the wrong type of values
        Then I should see 'type validation' errors
