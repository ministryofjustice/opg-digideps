Feature: PROF client search

  Background:
    Given "behat-prof-deputy-103-5@publicguardian.gov.uk" has been added to the "publicguardian.gov.uk" organisation
    And the organisation "publicguardian.gov.uk" is active

  @prof @prof-search @prof-client-search
  Scenario: Search broadly across clients by firstname or lastname
    Given I am logged in as "behat-prof-deputy-103-5@publicguardian.gov.uk" with password "Abcd1234"
    When I search for a client with the term "CLY3039"
    Then I should see "Showing 0 clients"
    When I search for a client with the term "CLY3"
    Then I should see "Showing 4 clients"
    When I search for a client with the term "hent309"
    Then I should see "Showing 0 clients"
    When I search for a client with the term "hent3"
    Then I should see "Showing 4 clients"

  @prof @prof-search @prof-client-search
  Scenario: Search exact name match across clients
    Given I am logged in as "behat-prof-deputy-103-5@publicguardian.gov.uk" with password "Abcd1234"
    When I search for a client with the term "ly302 Hent302"
    Then I should see "Showing 0 clients"
    When I search for a client with the term "cly302 Hent30"
    Then I should see "Showing 0 clients"
    When I search for a client with the term "cly302 Hent302"
    Then I should see "Showing 1 client"

