Feature: Admin unsubmit report (from client page)

  @deputy
  Scenario: Admin finds client in client search page
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
#    And I click on "admin-client-search"
# TODO use new link instead when available
    And I go to admin page "/admin/client/search"
    Then each text should be present in the corresponding region:
      | 8 clients | client-search-count |
    Then each text should be present in the corresponding region:
      | Cly Hent | client-name-9 |
      | behat001 | client-caseNumber-9 |
    When I fill in the following:
      | search_clients_q | hent |
    And I click on "search_clients_search"
    Then each text should be present in the corresponding region:
      | Cly Hent | client-behat001-fullname|
      | behat001 | client-behat001-caseNumber |
    And I click on "client-behat001-fullname"
    Then the URL should match "/admin/client/\d+/details"
    And I should see the "client-behat001-2016" region
    And I should not see the "client-behat001-2017" region
    And I should see "READY TO SUBMIT" in the client-behat001-2017" region
    And I save the current URL as "admin-client-search-client-behat001"

  @deputy
  Scenario: Cly Hent submit the 2016 report
    And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-start, report-submit"
    When I fill in the following:
      | report_declaration_agree | 1 |
      | report_declaration_agreedBehalfDeputy_0 | only_deputy |
      | report_declaration_agreedBehalfDeputyExplanation |  |
    And I press "report_declaration_save"
    Then the form should be valid

  @deputy
  Scenario: Client search check two reports are shown
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I go to the URL previously saved as "admin-client-search-client-behat001"
    Then I should see the "client-behat001-2016" region
    And I should see "SUBMITTED" in the client-behat001-2016" region
    Then I should see the "client-behat001-2017" region
