Feature: User management

  @admin @user-management
  Scenario: Admin user views the edit page of a Lay Deputy
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "user-behat-lay-deputy-102publicguardiangovuk"
    Then the response status code should be 200
    And I should see "Clients and reports"
    And I should not see "organisation"

  @admin @user-management
  Scenario: Admin user views the edit page of a PA Deputy
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And "behat-pa-deputy-103-4-6@publicguardian.gov.uk" has been added to the "abc-solicitors.example.com" organisation
    And I click on "user-behat-pa-deputy-103-4-6publicguardiangovuk"
    Then the response status code should be 200
    And I should not see "Clients and reports"
    And I should see "organisation"

  @admin @user-management
  Scenario: Admin user views the edit page of a Prof Deputy
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And "behat-prof-deputy-102-4-5@publicguardian.gov.uk" has been added to the "abc-solicitors.example.com" organisation
    And I click on "user-behat-prof-deputy-102-4-5publicguardiangovuk"
    Then the response status code should be 200
    And I should not see "Clients and reports"
    And I should see "organisation"
