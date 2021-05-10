@v2 @admin-management
Feature: Admin - Admin users delete admin users

  Scenario: A super admin user deletes other admin users
    Given a super admin user accesses the admin app
    When I navigate to the admin user edit page for an existing super admin user
    Then I should be able to delete the user
    When I attempt to delete an existing super admin user
    Then the user should be deleted
    When I attempt to delete an existing elevated admin user
    Then the user should be deleted
    When I attempt to delete an existing admin user
    Then the user should be deleted

  Scenario: An elevated admin user deletes other admin users
    Given an elevated admin user accesses the admin app
    When I attempt to delete an existing super admin user
    Then the user should not be deleted
    When I attempt to delete an existing elevated admin user
    Then the user should be deleted
    When I attempt to delete an existing admin user
    Then the user should be deleted

  Scenario: An admin user deletes other admin users
    Given an admin user accesses the admin app
    When I attempt to delete an existing super admin user
    Then the user should not be deleted
    When I attempt to delete an existing elevated admin user
    Then the user should not be deleted
    When I attempt to delete an existing admin user
    Then the user should be deleted
