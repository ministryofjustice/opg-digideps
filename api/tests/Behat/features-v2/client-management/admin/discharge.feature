@v2 @admin-client-discharge
Feature: Admin - Client Discharge

@super-admin @pfa-high-submitted
  Scenario: A super admin user discharges a client
    Given a super admin user accesses the admin app
    When I visit the client details page for an existing client linked to a Lay deputy
    And I attempt to discharge the client
    Then the client should be discharged

@elevated-admin @pfa-high-submitted
  Scenario: An elevated admin user cannot discharge a client
    Given an elevated admin user accesses the admin app
    When I visit the client details page for an existing client linked to a Lay deputy
    And I attempt to discharge the client
    Then the client should not be discharged

@admin @prof-admin-submitted @pfa-high-submitted
  Scenario: An admin user cannot discharge a client
    Given an admin user accesses the admin app
    When I visit the client details page for an existing client linked to a deputy in an Organisation
    And I attempt to discharge the client
    Then the client should not be discharged
