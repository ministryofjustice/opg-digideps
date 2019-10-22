Feature: Admin NDR submitted

  @ndr
  Scenario: Admin client search returns NDR client
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search"
    Then each text should be present in the corresponding region:
    | Cly3 Hent3 | client-33333333 |
    # Cant check how many clients because number may change depending on how many suites are run
    | clients | client-search-count |
    # Test searching client name
    When I fill in the following:
    | search_clients_q | hent3 |
    And I click on "search_clients_search"
    Then I should see "33333333"
    And each text should be present in the corresponding region:
    | Cly3 Hent3 | client-33333333 |
    | CLY3 HENT3 | client-31498120 |
    | Cly301 Hent301 | client-02200001 |
    # Test searching by case number
    When I fill in the following:
    | search_clients_q | 33333333 |
    And I click on "search_clients_search"
    And each text should be present in the corresponding region:
    | Cly3 Hent3 | client-33333333 |
    And I click on "client-details" in the "client-33333333" region
    And I save the current URL as "admin-client-search-client-33333333"

  @ndr
  Scenario: Admin client page shows NDR report complete
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "admin-client-search"
    And I fill in "search_clients_q" with "ndr"
    And I follow "John ndr-client"
    Then the URL should match "/admin/client/\d+/details"
    And I should see the "report-ndr" region in the "report-group-done" region
    And I should see "NDR" in the "report-ndr" region
