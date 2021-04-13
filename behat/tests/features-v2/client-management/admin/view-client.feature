@v2 @admin-client-view
Feature: Admin - View client details

  Scenario: An admin user views client details associated with a Lay deputy
    Given an admin user accesses the admin app
    When I visit the client details page for an existing client linked to a Lay deputy
    Then I should see the clients court order number
    And I should see the deputies name, address and contact details
    And I should see the reports associated with the client
    And I should not see "Discharge deputy"

  Scenario: An admin user views client details associated with an Org deputy
    Given an admin user accesses the admin app
    When I visit the client details page for an existing client linked to deputy in an Organisation
    Then I should see the clients court order
    And I should see the deputies name, address and contact details
    And I should see the reports associated with the client

  Scenario: A super admin user views client details associated with a Lay deputy
    Given a super admin user accesses the admin app
    When I visit the client details page for an existing client linked to a Lay deputy
    And I should see "Discharge deputy"
