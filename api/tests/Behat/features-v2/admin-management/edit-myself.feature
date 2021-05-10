@v2 @admin-management
Feature: Admin - An admin user edits their details

  Scenario: A super admin user updates their details
    Given a super admin user accesses the admin app
    When I navigate to the Your Details page
    And I update my firstname and lastname
    Then my details should be updates

  Scenario: An elevated admin user updates their details
    Given an elevated admin user accesses the admin app
    When I view the Your Details page
    And I update my firstname and lastname
    Then my details should be updates

  Scenario: An admin user updates their details
    Given an admin user accesses the admin app
    When I view the Your Details page
    And I update my firstname and lastname
    Then my details should be updates
