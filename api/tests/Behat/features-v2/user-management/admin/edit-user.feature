@v2 @admin-user-edit @admin-user
Feature: Admin - User Edit

    @super-admin
    Scenario: A super admin user edits each user
        Given a super admin user accesses the admin app
        And I have created the appropriate test users to edit
        When I edit each of the test users
        Then I should see the users have been correctly updated
        When I navigate to the lay user for edit tests
        Then I see user details are displayed correctly

    @super-admin
    Scenario: A super admin user tries to delete users
        Given a super admin user accesses the admin app
        And I have created the appropriate test users to edit
        When I delete the admin manager
        Then I no longer see the admin manager in search results
        When I delete the admin
        Then I no longer see the admin in search results

    @admin-manager
    Scenario: A admin manager user edits other admins
        Given an admin manager user accesses the admin app
        And I have created the appropriate test users to edit
        When I view the super admin user
        Then I should not be able to edit that user
        When I view the admin manager user
        Then I should not be able to edit that user
        But I should be able to delete that user
        When I view the admin user
        Then I should be able to edit that user

    @admin
    Scenario: A admin user edits a super admin
        Given an admin user accesses the admin app
        And I have created the appropriate test users to edit
        When I view the super admin user
        Then I should not be able to edit that user
        When I view the admin manager user
        Then I should not be able to edit that user
        When I view the admin user
        Then I should be able to edit that user
