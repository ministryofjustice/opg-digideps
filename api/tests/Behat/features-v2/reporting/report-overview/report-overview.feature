@v2 @report-overview
Feature: Report Overview - All User Roles

@prof-admin-not-started
  Scenario: A Professional User can see client and deputy details
    Given a Professional Admin Deputy has not started a report
    When I view the report overview page
    Then I should see the correct client details
    And I should see the correct deputy details

@pa-admin-not-started
  Scenario: A Public Authority User can see client and deputy details
    Given a Public Authority Admin Deputy has not started a report
    When I view the report overview page
    Then I should see the correct client details
    And I should see the correct deputy details
