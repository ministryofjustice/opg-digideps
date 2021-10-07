@v2 @v2_admin @admin-case-search
Feature: Admin - Case Search

  #@behat-test-user @prof-admin-health-welfare-not-started
  #Scenario: An admin user searches for an existing case by first name

  @behat-test-user @prof-admin-health-welfare-not-started
  Scenario: An admin user searches for an existing case by last name
    Given a behat test admin accesses the admin app
    When I visit the admin clients search page
    And I search for an existing client by their last name
    Then I should see the case details in the case list results

  @behat-test-user @prof-admin-health-welfare-not-started
  Scenario: An admin user searches for an existing case by case number
    Given a behat test admin accesses the admin app
    When I visit the admin clients search page
    And I search for an existing client by their case number
    Then I should see the case details in the case list results

  @behat-test-user @prof-admin-health-welfare-not-started
  Scenario: An admin user searches for a non-existent case
    Given a behat test admin accesses the admin app
    When I visit the admin clients search page
    And I search for a non-existent case
    Then I should see No Cases Found in the cases list results

  #@behat-test-user @lay-pfa-high-not-started
  #Scenario: An admin user searches for an existing case by first name when two clients have the same first name

  @behat-test-user @lay-pfa-high-not-started
  Scenario: An admin user searches for an existing case by last name when two clients have the same last name
    Given a behat test admin accesses the admin app
    And two clients have the same last name
    When I visit the admin clients search page
    And I search for an existing client by their last name
    Then I should see both case details in the case list results

  #@behat-test-user @paper-report
  #Scenario: An admin user searches for a paper reporting case by first name

  #@behat-test-user @paper-report
  #Scenario: An admin user searches for a paper reporting case by last name

  #@behat-test-user @paper-report
  #Scenario: An admin user searches for a paper reporting case by case number
