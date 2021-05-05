Feature: User management

  @admin @user-management
  Scenario: Admin user views the edit page of a Lay Deputy
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "user-behat-lay-deputy-102publicguardiangovuk"
    Then the response status code should be 200
    And I should see "Clients and reports"

  @admin @user-management
  Scenario: Admin user views the edit page of a Deputy in an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And "behat-pa-deputy-103-4-6@publicguardian.gov.uk" has been added to the "abc-solicitors.example.com" organisation
    And "behat-pa-deputy-103-4-6@publicguardian.gov.uk" has been added to the "some.example.com" organisation
    And the organisation "abc-solicitors.example.com" is active
    And the organisation "some.example.com" is active
    And I click on "user-behat-pa-deputy-103-4-6publicguardiangovuk"
    Then the response status code should be 200
    And I should not see "Clients and reports"
    When I follow "john.smith@abc-solicitors.example.com"
    Then the url should match "/admin/organisations/\d+"
    And I should see "john.smith@abc-solicitors.example.com"
    When I move backward one page
    And I follow "Domain-owning organisation"
    Then the url should match "/admin/organisations/\d+"
    And I should see "Domain-owning organisation"

  @admin @user-management
  Scenario: Admin user views the edit page of a non lay Deputy who is not in an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And "behat-pa-deputy-103-4-6@publicguardian.gov.uk" has been removed from the "abc-solicitors.example.com" organisation
    And "behat-pa-deputy-103-4-6@publicguardian.gov.uk" has been removed from the "some.example.com" organisation
    And I click on "user-behat-pa-deputy-103-4-6publicguardiangovuk"
    Then the response status code should be 200
    And I should not see "Clients and reports"
    And I should not see "john.smith@abc-solicitors.example.com"
    And I should not see "Domain-owning organisation"
