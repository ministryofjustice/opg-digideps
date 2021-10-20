@v2 @v2_admin @admin-court-order-search
Feature: Admin - Court Order Search

  @behat-test-user @prof-admin-health-welfare-not-started
  Scenario: An admin user searches for an existing digitally reporting court order by first name
    Given a behat test admin accesses the admin app
    When I visit the admin clients search page
    And I search for an existing client by their first name
    Then I should see No Court Orders Found in the court order list results

  @behat-test-user @prof-admin-health-welfare-not-started
  Scenario: An admin user searches for an existing digitally reporting court order by last name
    Given a behat test admin accesses the admin app
    When I visit the admin clients search page
    And I search for an existing client by their last name
    Then I should see the court order details in the court order list results

  @behat-test-user @prof-admin-health-welfare-not-started
  Scenario: An admin user searches for an existing digitally reporting court order by case number
    Given a behat test admin accesses the admin app
    When I visit the admin clients search page
    And I search for an existing client by their case number
    Then I should see the court order details in the court order list results

  @behat-test-user @prof-admin-health-welfare-not-started
  Scenario: An admin user searches for a non-existent court order
    Given a behat test admin accesses the admin app
    When I visit the admin clients search page
    And I search for a non-existent court order
    Then I should see No Court Orders Found in the court order list results

  @behat-test-user @lay-pfa-high-not-started
  Scenario: An admin user searches for an existing court order by last name when two clients have the same last name
    Given a behat test admin accesses the admin app
    And two clients have the same last name
    When I visit the admin clients search page
    And I search for an existing client by their last name
    Then I should see both court order details in the court order list results

  @behat-test-user
  Scenario: An admin user searches for a paper reporting court order by last name
      Given a behat test admin accesses the admin app
      When I visit the admin clients search page
      And I search for a paper reporting court order by their last name
      Then I should see the paper court order details in the court order list results

  @behat-test-user
  Scenario: An admin user searches for a paper reporting court order by case number
      Given a behat test admin accesses the admin app
      When I visit the admin clients search page
      And I search for a paper reporting court order by their case number
      Then I should see the paper court order details in the court order list results
