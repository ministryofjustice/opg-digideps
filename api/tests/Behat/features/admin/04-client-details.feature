@admin
Feature: Client details

  Scenario: Client information contains report type
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I visit the client page for "102-4-6"
    Then I should see "OPG102-4-6" in the "report-2016-to-2017" region

  Scenario: Client details contain named deputy information
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I visit the client page for "102-4-6"
    Then I should see "Named Deputy 102-4-6"
    And I should see "Victoria Road" in the "deputy-details" region
    And I should see "SW1" in the "deputy-details" region
    And I should see "GB" in the "deputy-details" region
    And I should see "07911111111111" in the "deputy-details" region
    And I should see "behat-nd-102-4-6@publicguardian.gov.uk" in the "deputy-details" region

  Scenario: Lay client details contain named deputy information
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I visit the client page for "103"
    Then I should see "Lay Deputy 103"
    And I should see "Victoria Road" in the "deputy-details" region
    And I should see "SW1" in the "deputy-details" region
    And I should see "GB" in the "deputy-details" region
    And I should see "07911111111111" in the "deputy-details" region
    And I should see "behat-lay-deputy-103@publicguardian.gov.uk" in the "deputy-details" region
