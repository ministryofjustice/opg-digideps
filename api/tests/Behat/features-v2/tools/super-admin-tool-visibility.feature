@v2 @v2_admin @tools
Feature: Restricting visibility of Tools to super-admins

    #Not tested, just outlining test cases and steps
    @super-admin
    Scenario: A super admin has visibility of the Tools link
        Given a super admin user accesses the admin app
        Then I should see the Tools link

    #Not tested, just outlining test cases and steps
    @admin
    Scenario: An admin user does not have visibility of the Tools link
        Given an admin user accesses the admin app
        Then I should not see the Tools link
