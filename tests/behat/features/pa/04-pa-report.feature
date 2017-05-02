@pa @pareport
Feature: PA report

  Scenario: PA user links to report
    Given I load the application status from "pa-users-uploaded"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then the "Hent, Cly7" link, in the "client-1000014" region, url should contain "/report/7/overview"
    When I click on "pa-report-open" in the "client-1000014" region
    Then the response status code should be 200
    And the URL should match "report/7/overview"