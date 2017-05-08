Feature: PA cannot access other's PA's reports and clients
# team1 = team with ID=1 and behat-pa1* email for members


  Scenario: PA reload status after team1 has been complete
    Given I load the application status from "team-users-complete"

  Scenario: Assert team1 can only access its reports
    # Named PA
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-1000010" region
    Then the response status code should be 200
    And the URL should match "report/1/overview"
    And I should not see the "client-2000003" region
    # Admin
    Given I am logged in as "behat-pa1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-1000010" region
    Then the response status code should be 200
    And the URL should match "report/1/overview"
    And I should not see the "client-2000003" region
    # team member
    Given I am logged in as "behat-pa1-team-member@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-1000010" region
    Then the response status code should be 200
    And the URL should match "report/1/overview"
    And I should not see the "client-2000003" region

  Scenario: Named PA from second team can only access his data
    # can access team2 reports
    Given I am logged in as "behat-pa2@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-2000001" region
    Then the response status code should be 200
    And the URL should match "report/\d+/overview"
    # cannot access team1 reports
    And I should not see the "client-1000010" region
    When I go to "report/1/overview"
    Then the response status code should be 500
