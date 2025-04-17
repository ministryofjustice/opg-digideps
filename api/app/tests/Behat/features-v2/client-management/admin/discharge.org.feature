@v2 @v2_admin @admin-client-discharge
Feature: Admin - Client Discharge

@super-admin @prof-admin-health-welfare-submitted
  Scenario: A super admin user discharges an org client
    Given a super admin user accesses the admin app
    When I visit the admin client details page for an existing client linked to a deputy in an Organisation
    And I attempt to discharge the client
    Then the client should be discharged
    And I can see a count of active and discharged clients on the organisations page

@admin-manager @prof-admin-health-welfare-submitted
  Scenario: An admin manager user discharges an org client
    Given an admin manager user accesses the admin app
    When I visit the admin client details page for an existing client linked to a deputy in an Organisation
    And I attempt to discharge the client
    Then the client should be discharged

@admin @prof-admin-health-welfare-submitted
  Scenario: An admin user discharges an org client
    Given an admin user accesses the admin app
    When I visit the admin client details page for an existing client linked to a deputy in an Organisation
    And I attempt to discharge the client
    Then the client should not be discharged

@admin-manager @prof-admin-health-welfare-submitted
Scenario: An admin manager user discharges an org client without a named deputy
    Given a Professional Deputy has submitted a Health and Welfare report
    And an admin manager user accesses the admin app
    And the client does not have a named deputy associated with them
    When I visit the admin client details page for an existing client linked to a deputy in an Organisation
    And I attempt to discharge the client
    Then the client should be discharged
