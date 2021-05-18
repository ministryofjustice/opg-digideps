@v2 @admin-management
Feature: Admin - An admin user edits other admins details

  Scenario: A super admin user updates other admin users details
    Given a super admin user accesses the admin app
    And another super admin user exists
    When I attempt to update an existing "super admin" users details
    Then the users details should be updated
    When I attempt to update an existing "admin manager" users details
    Then the users details should be updated
    When I attempt to update an existing admin users details
    Then the users details should be updated

  Scenario: An admin manager user updates other admin users details
    Given an admin manager user accesses the admin app
    And another admin manager user exists
    When I attempt to update an existing "admin manager" users details
    Then the users details should not be updated
    When I attempt to update an existing "super admin" users details
    Then the users details should not be updated
    When I attempt to update an existing "admin" users details
    Then the users details should be updated

  Scenario: An admin user updates other admin users details
    Given an admin user accesses the admin app
    And another admin user exists
    When I attempt to update an existing "admin" users details
    Then the users details should be updated
    When I attempt to update an existing "super admin" users details
    Then the users details should not be updated
    When I attempt to update an existing "admin manager" users details
    Then the users details should not be updated
