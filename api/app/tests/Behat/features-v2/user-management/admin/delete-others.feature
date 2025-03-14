@v2 @v2_admin @admin-management
Feature: Admin - Admin users delete admin users

  @super-admin @admin-manager @admin
  Scenario: A super admin user deletes other admin users
    Given a super admin user accesses the admin app
    And another super admin user exists
    When I attempt to delete an existing "super admin" user
    Then the user should be deleted
    When I attempt to delete an existing "admin manager" user
    Then the user should be deleted
    When I attempt to delete an existing "admin" user
    Then the user should be deleted

  @super-admin @admin-manager @admin
  Scenario: An admin manager user deletes other admin users
    Given an admin manager user accesses the admin app
    And another admin manager user exists
    When I attempt to delete an existing "admin manager" user
    Then the user should be deleted
    When I attempt to delete an existing "super admin" user
    Then the user should not be deleted
    When I attempt to delete an existing "admin" user
    Then the user should be deleted

  @super-admin @admin-manager @admin
  Scenario: An admin user deletes other admin users
    Given an admin user accesses the admin app
    And another admin user exists
    When I attempt to delete an existing "admin" user
    Then the user should not be deleted
    When I attempt to delete an existing "admin manager" user
    Then the user should not be deleted
    When I attempt to delete an existing "super admin" user
    Then the user should not be deleted
