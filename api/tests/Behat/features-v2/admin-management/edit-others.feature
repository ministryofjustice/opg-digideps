@v2 @v2_admin @admin-management
Feature: Admin - An admin user edits other admins details

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

    @super-admin
    Scenario: A super admin user enters invalid data
        Given a super admin user accesses the admin app
        And another admin user exists
        When I visit the admin Edit User page for the user I'm interacting with
        And I enter empty values
        Then I should see 'missing values' errors
        When I enter the wrong type of values
        Then I should see 'type validation' errors
