@v2 @v2_admin @admin-client-search
Feature: Admin - Client Search

  @admin @prof-admin-health-welfare-not-started
  Scenario: An admin user searches for a client by first name
    Given an admin user accesses the admin app
    When I navigate to the admin clients search page
    And I search for an existing client by their first name
    Then I should see the clients details in the client list results

  @admin @prof-admin-health-welfare-not-started
  Scenario: An admin user searches for a client by last name
    Given an admin user accesses the admin app
    When I visit the admin clients search page
    And I search for an existing client by their last name
    Then I should see the clients details in the client list results

  @admin @prof-admin-health-welfare-not-started
  Scenario: An admin user searches for a client by case number
    Given an admin user accesses the admin app
    When I visit the admin clients search page
    And I search for an existing client by their case number
    Then I should see the clients details in the client list results
    And I should see the correct count of clients in the client list results

  @admin @prof-admin-health-welfare-not-started
  Scenario: An admin user searches for a non-existent client
    Given an admin user accesses the admin app
    When I visit the admin clients search page
    And I search for an non-existent client
    Then I should see No Clients Found in the client list results

  @admin @prof-admin-health-welfare-not-started @lay-pfa-high-not-started
  Scenario: An admin user searches for a client by first name when two clients have the same first name
    Given an admin user accesses the admin app
    And two clients have the same first name
    When I visit the admin clients search page
    And I search for an existing client by their first name
    Then I should see both the clients details in the client list results

  @admin @prof-admin-health-welfare-not-started @lay-pfa-high-not-started
  Scenario: An admin user searches for a client by last name when two clients have the same last name
    Given an admin user accesses the admin app
    And two clients have the same last name
    When I visit the admin clients search page
    And I search for an existing client by their last name
    Then I should see both the clients details in the client list results

  @admin @prof-admin-health-welfare-not-started @lay-pfa-high-not-started
  Scenario: An admin user searches for a client by full name when two clients have the same full name
    Given an admin user accesses the admin app
    And two clients have the same last name
    When I visit the admin clients search page
    And I search for an existing client by their full name
    Then I should see both the clients details in the client list results
    And I should see the correct count of clients in the client list results
