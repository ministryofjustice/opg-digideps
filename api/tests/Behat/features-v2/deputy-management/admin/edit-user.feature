@v2 @admin-deputy-management
Feature: Admin - Edit deputy users

  Scenario: A super admin user edits a deputy users details
    Given a super admin user accesses the admin app
    And a Lay Deputy exists
    Then I should be able to update the deputies firstname, lastname, postcode and email address
    When I update the details of the deputy available to me
    Then the deputies details should be updated

  Scenario: An elevated admin user edits a deputy users details
    Given an elevated admin user accesses the admin app
    And a Lay Deputy exists
    Then I should be able to update the deputies firstname, lastname, postcode and email address
    When I update the details of the deputy available to me
    Then the deputies details should be updated

  Scenario: An admin user edits a deputy users details
    Given an admin user accesses the admin app
    And a Lay Deputy exists
    Then I should be able to update the deputies firstname, lastname and postcode
    And I should not be able to update the deputies email address
    When I update the details of the deputy available to me
    Then the deputies details should be updated
