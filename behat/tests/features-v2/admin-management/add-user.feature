@v2 @admin-management
Feature: Admin - An admin user adds an admin user

  Scenario: A super admin user adds other admin users
    Given a super admin user accesses the admin app
    When I navigate to the Edit User page for an
    Then I should be able to add a super admin user
    And I should be able to add an elevated admin user
    And I should be able to add an admin user
    When I add a new super admin user
    Then the new super admin user should be added
    When I add a new elevated admin user
    Then the new elevated admin user should be added

  Scenario: An elevated admin user adds other admin users
    Given an elevated admin user accesses the admin app
    When I visit the Add Users page
    Then I should not be able to add a super admin user
    And I should not be able to add an elevated admin user
    And I should be able to add an admin user
    When I add a new admin user
    Then the new admin user should be added

  Scenario: An admin user adds other admin users
    Given an admin user accesses the admin app
    When I visit the Add Users page
    Then I should not be able to add a super admin user
    And I should not be able to add an elevated admin user
    And I should be able to add an admin user
    When I add a new admin user
    Then the new admin user should be added
