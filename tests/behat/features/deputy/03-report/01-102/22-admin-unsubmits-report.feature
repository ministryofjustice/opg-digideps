Feature: Admin unsubmit report (from client page)

  @deputy
  Scenario: Admin client page + search
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search"
    Then each text should be present in the corresponding region:
      | 8 clients | client-search-count |
    Then each text should be present in the corresponding region:
      | Cly Hent | client-behat001 |
    When I fill in the following:
      | search_clients_q | hent |
    And I click on "search_clients_search"
    Then I should see the "client-row" region exactly "1" times
    And each text should be present in the corresponding region:
      | Cly Hent | client-behat001 |
    And I click on "client-details" in the "client-behat001" region
    And I save the current URL as "admin-client-search-client-behat001"

  @deputy
  Scenario: Admin report page + unsubmit report
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I go to the URL previously saved as "admin-client-search-client-behat001"
    # reports page
    Then the URL should match "/admin/client/\d+/details"
    And I should see "SUBMITTED" in the "client-behat001-2016" region
    And I should see "NOT FINISHED" in the "client-behat001-2017" region
    When I click on "manage" in the "behat001-2016" region
    And I press "unsubmit_report_save"
    Then the current URL should match with the URL previously saved as "admin-client-search-client-behat001"
    And I should see "unsubmitted" in the "client-behat001-2016" region

