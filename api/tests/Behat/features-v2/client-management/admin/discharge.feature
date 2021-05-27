@v2 @admin-client-discharge
Feature: Admin - Client Discharge

  Scenario: A super admin user discharges a client
    Given a super admin user accesses the admin app
    When I visit the admin client details page for an existing client linked to a Lay deputy
    And I attempt to discharge the client
    Then the client should be discharged

  Scenario: An admin manager user can discharge a client
    Given an admin manager user accesses the admin app
    When I visit the admin client details page for an existing client linked to a Lay deputy
    And I attempt to discharge the client
    Then the client should be discharged

  Scenario: An admin user cannot discharge a client
    Given an admin user accesses the admin app
    When I visit the admin client details page for an existing client linked to a deputy in an Organisation
    And I attempt to discharge the client
    Then the client should not be discharged
