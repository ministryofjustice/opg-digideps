Feature: PA report

  Scenario: PA user links to report
    Given I load the application status from "pa-users-uploaded"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-1000014" region
    Then the response status code should be 200
    And the URL should match "report/\d+/overview"