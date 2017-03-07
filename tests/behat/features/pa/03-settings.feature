Feature: PA setting pages

  Scenario: team page
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings" in the navbar region
    # settings page
    And I click on "user-accounts"
    Then I should see the "team-user-behat-pa1publicguardiangsigovuk" region
    

