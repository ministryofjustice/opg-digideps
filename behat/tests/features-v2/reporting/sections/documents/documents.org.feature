@v2
Feature: Documents - Org User Roles

  Scenario: Organisation users should see guidance on sending the previous years cost certificate
    Given a Professional Admin Deputy has not started a report
    When I view the documents report section
    Then I should see guidance on providing the final cost certificate for the previous reporting period
