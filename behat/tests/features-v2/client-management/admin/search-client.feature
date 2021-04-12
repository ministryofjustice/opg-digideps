@v2 @admin-client-search
Feature: Admin - Client Search

  Scenario: An admin user searches for a client by first name
    Given an admin user accesses the admin app
    When I navigate to the admin clients search page
    And I search for an existing client by their first name
    Then I should see the clients details in the client list results

  Scenario: An admin user searches for a client by last name
    Given an admin user accesses the admin app
    When I visit the clients search page
    And I search for an existing client by their last name
    Then I should see the clients details in the client list results

  Scenario: An admin user searches for a client by case number
    Given an admin user accesses the admin app
    When I visit the clients search page
    And I search for an existing client by their case number
    Then I should see the clients details in the client list results

  Scenario: An admin user searches for a client by first name two clients have the same first name
    Given an admin user accesses the admin app
    And two clients have the same first name
    When I visit the clients search page
    And I search for an existing client by their first name
    Then I should see both the clients details in the client list results

  Scenario: An admin user searches for a client by last name two clients have the same last name
    Given an admin user accesses the admin app
    And two clients have the same last name
    When I visit the clients search page
    And I search for an existing client by their last name
    Then I should see both the clients details in the client list results
