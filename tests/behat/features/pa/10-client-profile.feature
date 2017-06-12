Feature: PA client profile

  Scenario: PA view client details
    Given I load the application status from "team-users-complete"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000010" region
    Then I should see the "client-profile-details" region
    And I should see "Cly1 Hent1" in the "client-profile-details" region
    And I should see "01 Jan 1967" in the "client-profile-details" region
    And I should see "078912345678" in the "client-profile-details" region
    And I should see "cly1@hent.com" in the "client-profile-details" region
    And I should see "B301QL" in the "client-profile-details" region
