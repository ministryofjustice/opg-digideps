@v2 @report-overview
Feature: Report Overview - All User Roles

@prof-admin-not-started
  Scenario: A Professional User can see client and deputy details
    Given a Professional Admin Deputy has not started a report
    When I view the report overview page
    Then I should see the correct client details
    And I should see the correct deputy details

@prof-team-hw-not-started
  Scenario: A user uploads multiple supporting document that have valid file types
    Given a Professional Team Deputy has not started a health and welfare report
    When I view the report overview page
    Then I should see the correct client details
    And I should see the correct deputy details
