Feature: PROF client search
  @prof @prof-search @prof-client-search
  Scenario: Search broadly across clients by firstname or lastname
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234!!"
    When I search for a client with the term "Updateds"
    Then I should see "Showing 0 clients"
    When I search for a client with the term "Updated"
    Then I should see "Showing 1 client"
    When I search for a client with the term "Name"
    Then I should see "Showing 1 client"

  @prof @prof-search @prof-client-search
  Scenario: Search exact name match across clients
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234!!"
    When I search for a client with the term "ame Updated"
    Then I should see "Showing 0 clients"
    When I search for a client with the term "Name Update"
    Then I should see "Showing 0 clients"
    When I search for a client with the term "Name Updated"
    Then I should see "Showing 1 client"
