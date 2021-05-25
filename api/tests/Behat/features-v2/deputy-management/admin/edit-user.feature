@v2 @admin-deputy-management
Feature: Admin - Edit deputy users

    Scenario: A super admin user edits a deputy users details
        Given a super admin user accesses the admin app
        And a Lay Deputy exists
        Then I should be able to update the deputies firstname, lastname, postcode and email address
        When I update the details of the deputy available to me
        Then the deputies details should be updated

    Scenario: An admin manager user edits a deputy users details
        Given an admin manager user accesses the admin app
        And a Lay Deputy exists
        Then I should be able to update the deputies firstname, lastname, postcode and email address
        When I update the details of the deputy available to me
        Then the deputies details should be updated

    Scenario: An admin user edits a deputy users details
        Given an admin user accesses the admin app
        And a Lay Deputy exists
        Then I should be able to update the deputies firstname, lastname and postcode
        And I should not be able to update the deputies email address
        When I update the details of the deputy available to me
        Then the deputies details should be updated

    Scenario: A super admin user enters invalid data
        Given a super admin user accesses the admin app
        And a Lay Deputy exists
        When I visit the admin Edit User page for the user I'm interacting with
        And I enter empty values
        Then I should see 'missing values' errors
        When I enter the wrong type of values
        Then I should see 'type validation' errors
