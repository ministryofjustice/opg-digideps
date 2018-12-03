Feature: PROF deputy costs estimate

  Scenario: add cost estimate
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    And die 2