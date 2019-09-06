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
    Then I should see the "client-row" region exactly "1" times
    And each text should be present in the corresponding region:
    | Cly3 Hent3 | client-33333333 |
    # Test searching by case number
    When I fill in the following:
    | search_clients_q | 33333333 |
    And I click on "search_clients_search"
    Then I should see the "client-row" region exactly "8" times
    And each text should be present in the corresponding region:
    | Cly3 Hent3 | client-33333333 |
    And I click on "client-details" in the "client-33333333" region
    And I save the current URL as "admin-client-search-client-33333333"

 Scenario: Admin client page shows NDR report
   Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
   And I go to the URL previously saved as "admin-client-search-client-33333333"
    # client details page
   Then the URL should match "/admin/client/\d+/details"
   Then I should see "SUBMITTED" in the "report-2016-label" region

