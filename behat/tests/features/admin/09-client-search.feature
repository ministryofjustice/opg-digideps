Feature: Client search
  @admin @admin-search @client-search
  Scenario: Search broadly across clients by firstname or lastname
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I search in admin for a client with the term "Johnny"
    Then I should see "Found 0 clients"
    When I search in admin for a client with the term "Joh"
    Then I should see "Found 22 clients"
    When I search in admin for a client with the term "-client"
    Then I should see "Found 22 clients"
    When I search in admin for a client with the term "102-4-client"
    Then I should see "Found 1 clients"

  @admin @admin-search @client-search
  Scenario: Search exact name match across clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I search in admin for a client with the term "John 102-4-"
    Then I should see "Found 0 clients"
    And I search in admin for a client with the term "John 102-4-client"
    Then I should see "Found 1 clients"
