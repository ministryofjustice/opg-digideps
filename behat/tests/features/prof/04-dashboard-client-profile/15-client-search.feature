Feature: PROF client search
  @prof @prof-search @prof-client-search
  Scenario: Search broadly across clients by firstname or lastname
    Given I am logged in as "behat-prof-deputy-103-5@publicguardian.gov.uk" with password "Abcd1234"
    When I search for a client with the term "Johnny"
    Then I should see "Showing 0 clients"
    When I search for a client with the term "Joh"
    Then I should see "Showing 1 client"
    When I search for a client with the term "103-5-Clien"
    Then I should see "Showing 1 client"

  @prof @prof-search @prof-client-search
  Scenario: Search exact name match across clients
    Given I am logged in as "behat-prof-deputy-103-5@publicguardian.gov.uk" with password "Abcd1234"
    When I search for a client with the term "ohn 103-5-client"
    Then I should see "Showing 0 clients"
    When I search for a client with the term "John 103-5-clien"
    Then I should see "Showing 0 clients"
    When I search for a client with the term "John 103-5-client"
    Then I should see "Showing 1 client"

