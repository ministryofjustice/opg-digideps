@v2 @v2_admin @admin-user-create @admin-user
Feature: Admin - User Create

@super-admin
  Scenario: A super admin user creates a deputy user
    Given a super admin user accesses the admin app
    When I add a new lay deputy user
    Then I see that an activation email has been sent to the user
    When I search for the newly created user
    Then I can see the user as non active in search results
    When I resend activation email
    Then I see that activation link has been sent

@super-admin
Scenario: A super admin creates admin users
    Given a super admin user accesses the admin app
    When I add each of the available user types for a super admin
    Then I should see each created users in the search window

@admin-manager
Scenario: A admin manager creates admin users
    Given an admin manager user accesses the admin app
    When I check we can add the appropriate user types for an admin manager
    Then I see the appropriate user types available to add

@admin
Scenario: A admin creates admin users
    Given an admin user accesses the admin app
    When I check we can add the appropriate user types for an admin
    Then I see the appropriate user types available to add

@super-admin
Scenario: A super admin user enters invalid details
    Given a super admin user accesses the admin app
    When I navigate to the add a new user page
    And I add invalid details in each of the fields
    Then I get the correct validation messages for invalid user
