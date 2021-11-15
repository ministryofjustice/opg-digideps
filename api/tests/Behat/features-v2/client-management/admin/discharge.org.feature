@v2 @v2_admin @admin-client-discharge
Feature: Admin - Client Discharge

@super-admin @prof-admin-health-welfare-submitted
  Scenario: A super admin user discharges a client
    Given a super admin user accesses the admin app
    When I visit the admin client details page for an existing client linked to a deputy in an Organisation
    And I attempt to discharge the client
    Then the client should be discharged

@admin-manager @prof-admin-health-welfare-submitted
  Scenario: An admin manager user can discharge a client
    Given an admin manager user accesses the admin app
    When I visit the admin client details page for an existing client linked to a deputy in an Organisation
    And I attempt to discharge the client
    Then the client should be discharged

@admin @prof-admin-health-welfare-submitted
  Scenario: An admin user cannot discharge a client
    Given an admin user accesses the admin app
    When I visit the admin client details page for an existing client linked to a deputy in an Organisation
    And I attempt to discharge the client
    Then the client should not be discharged
