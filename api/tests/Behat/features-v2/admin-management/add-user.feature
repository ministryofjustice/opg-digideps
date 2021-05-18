@v2 @admin-management
Feature: Admin - An admin user adds an admin user

  Scenario: A super admin user adds other admin users
    Given a super admin user accesses the admin app
    When I navigate to the admin add user page
    Then I should be able to add a super admin user
    And I should be able to add an admin manager user
    And I should be able to add an admin user
    When I enter valid details for a new super admin user
    And I submit the form
    Then the new user should be added
    When I enter valid details for a new admin manager user
    And I submit the form
    Then the new user should be added

  Scenario: An admin manager user adds other admin users
    Given an admin manager user accesses the admin app
    When I visit the admin Add Users page
    Then I should not be able to add a super admin user
    And I should not be able to add an admin manager user
    And I should be able to add an admin user
    When I enter valid details for a new admin user
    And I submit the form
    Then the new user should be added

  Scenario: An admin user adds other admin users
    Given an admin user accesses the admin app
    When I visit the admin Add Users page
    Then I should not be able to add a super admin user
    And I should not be able to add an admin manager user
    And I should be able to add an admin user
    When I enter valid details for a new admin user
    And I submit the form
    Then the new user should be added
