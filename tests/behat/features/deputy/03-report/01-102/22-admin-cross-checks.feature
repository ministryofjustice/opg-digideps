Feature: Admin cross checks

  @deputy
  Scenario: Client search results
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
#    And I click on "admin-client-search"
    And I go to admin page "/admin/client/search"
    Then each text should be present in the corresponding region:
      | 8 clients | client-search-count |
    Then each text should be present in the corresponding region:
      | Cly Hent | client-name-9 |
      | behat001 | client-caseNumber-9 |
    When I fill in the following:
      | search_clients_q | 104 |
    And I click on "search_clients_search"
    Then each text should be present in the corresponding region:
      | John 104 | client-name-6 |
      | test1040 | client-caseNumber-6 |

  @deputy
  Scenario: Client search result details
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
#    And I click on "admin-client-search"
    And I go to admin page "/admin/client/search"
    When I fill in the following:
      | search_clients_q | 104 |
    And I click on "search_clients_search"
    Then each text should be present in the corresponding region:
      | John 104 | client-name-6 |
      | test1040 | client-caseNumber-6 |
    And I click on "client-details-6"
      Then I should be on "/admin/client/6/details"
