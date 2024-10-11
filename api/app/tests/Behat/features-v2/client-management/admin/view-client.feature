@v2 @v2_admin @admin-client-view
Feature: Admin - View client details

  @admin @lay-pfa-high-submitted
  Scenario: An admin user views client details associated with a Lay deputy
    Given an admin user accesses the admin app
    Then I should not see "Client details"
    When I visit the admin client details page for an existing client linked to a Lay deputy
    Then I should see the clients court order number
    And I should see the Lay deputies name, address and contact details
    And I should see the reports associated with the client
    And I should not see "Discharge deputy"

  @admin @prof-admin-health-welfare-submitted
  Scenario: An admin user views client details associated with an Org deputy
    Given an admin user accesses the admin app
    When I visit the admin client details page for an existing client linked to a deputy in an Organisation
    Then I should see the clients court order number
    And I should see the organisation the deputy belongs to
    And I should see the name and email of the named deputy
    And I should see the reports associated with the client

  @super-admin @lay-pfa-high-submitted
  Scenario: A super admin user views client details associated with a Lay deputy
    Given a super admin user accesses the admin app
    When I visit the admin client details page for an existing client linked to a Lay deputy
    And I should see "Discharge deputy"
