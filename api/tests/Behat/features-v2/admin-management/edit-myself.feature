@v2 @admin-management
Feature: Admin - An admin user edits their details

  Scenario: A super admin user updates their details
    Given a super admin user accesses the admin app
    When I navigate to my admin user profile page
    And I update my firstname and lastname
    Then my details should be updated

  Scenario: An admin manager user updates their details
    Given an admin manager user accesses the admin app
    When I visit my admin user profile page
    And I update my firstname and lastname
    Then my details should be updated

  Scenario: An admin user updates their details
    Given an admin user accesses the admin app
    When I visit my admin user profile page
    And I update my firstname and lastname
    Then my details should be updated
