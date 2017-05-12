Feature: PA cannot access other's PA's reports and clients
# team1 = team with client 1000010
# team2 = team with client 2000003

  Scenario: PA reload status from the point where team1 has been fully added
    Given I load the application status from "team-users-complete"

  Scenario: Assert team1 can only access its reports
    # Named PA
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-1000010" region
    Then the response status code should be 200
    And the URL should match "report/\d+/overview"
    And I save the current URL as "report-for-client-1000010.url"
    But I should not see the "client-2000003" region
    # Admin
    Given I am logged in as "behat-pa1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-1000010" region
    Then the response status code should be 200
    And the current URL should match with the URL previously saved as "report-for-client-1000010.url"
    But I should not see the "client-2000003" region
    # team member
    Given I am logged in as "behat-pa1-team-member@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-1000010" region
    Then the response status code should be 200
    And the current URL should match with the URL previously saved as "report-for-client-1000010.url"
    But I should not see the "client-2000003" region

  Scenario: team2 can access its client but not team1's data
    # can access team2 reports
    Given I am logged in as "behat-pa2@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-2000001" region
    Then the response status code should be 200
    And the URL should match "report/\d+/overview"
    # cannot access team1 reports
    But I should not see the "client-1000010" region
    When I go to the URL previously saved as "report-for-client-1000010.url"
    Then the response status code should be 500

  Scenario: PA user cannot edit client
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then the URL "/settings" should be forbidden
    And the URL "/user-account/client-show" should be forbidden
    And the URL "/user-account/client-edit" should be forbidden
    And the URL "/user-account/user-show" should be forbidden
    And the URL "/user-account/user-edit" should be forbidden
    And the URL "/user-account/password-edit" should be forbidden