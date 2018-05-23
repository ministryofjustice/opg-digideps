Feature: Admin report checklist

  @deputy @shaun
  Scenario: Admin submits empty checklist for the report
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
    Then the URL should match "/admin/client/\d+/details"
    And I click on "checklist" in the "report-2016" region
    Then the URL should match "/admin/report/\d+/checklist"
    When I click on "submit-and-download"
    Then the following fields should have an error:
      | report_checklist_reportingPeriodAccurate_0        |
      | report_checklist_reportingPeriodAccurate_1        |
      | report_checklist_contactDetailsUptoDate        |
      | report_checklist_deputyFullNameAccurateinCasrec        |
      | report_checklist_decisionsSatisfactory_0        |
      | report_checklist_decisionsSatisfactory_1        |
      | report_checklist_consultationsSatisfactory_0        |
      | report_checklist_consultationsSatisfactory_1        |
      | report_checklist_careArrangements_0        |
      | report_checklist_careArrangements_1        |
      | report_checklist_assetsDeclaredAndManaged_0        |
      | report_checklist_assetsDeclaredAndManaged_1        |
      | report_checklist_assetsDeclaredAndManaged_2        |
      | report_checklist_debtsManaged_0        |
      | report_checklist_debtsManaged_1        |
      | report_checklist_debtsManaged_2        |
      | report_checklist_openClosingBalancesMatch_0        |
      | report_checklist_openClosingBalancesMatch_1        |
      | report_checklist_openClosingBalancesMatch_2        |
      | report_checklist_accountsBalance_0        |
      | report_checklist_accountsBalance_1        |
      | report_checklist_accountsBalance_2        |
      | report_checklist_moneyMovementsAcceptable_0        |
      | report_checklist_moneyMovementsAcceptable_1        |
      | report_checklist_moneyMovementsAcceptable_2        |
      | report_checklist_bondAdequate_0        |
      | report_checklist_bondAdequate_1        |
      | report_checklist_bondAdequate_2        |
      | report_checklist_bondOrderMatchCasrec_0        |
      | report_checklist_bondOrderMatchCasrec_1        |
      | report_checklist_bondOrderMatchCasrec_2        |
      | report_checklist_futureSignificantFinancialDecisions_0        |
      | report_checklist_futureSignificantFinancialDecisions_1        |
      | report_checklist_futureSignificantFinancialDecisions_2        |
      | report_checklist_hasDeputyRaisedConcerns_0        |
      | report_checklist_hasDeputyRaisedConcerns_1        |
      | report_checklist_hasDeputyRaisedConcerns_2        |
      | report_checklist_caseWorkerSatisified_0        |
      | report_checklist_caseWorkerSatisified_1        |
      | report_checklist_caseWorkerSatisified_2        |
      | report_checklist_finalDecision_0        |
      | report_checklist_finalDecision_1        |
      | report_checklist_finalDecision_2        |
      | report_checklist_finalDecision_3        |
      | report_checklist_lodgingSummary         |
    And the URL should match "/admin/report/\d+/checklist"

  @deputy @shaun
  Scenario: Admin saves further information on checklist
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
    Then the URL should match "/admin/client/\d+/details"
    And I click on "checklist" in the "report-2016" region
    Then the URL should match "/admin/report/\d+/checklist"
#    And I fill in "report_checklist_furtherInformation" with "Some more info"
#    When I click on "save-further-information"
#    Then the form should be valid
#    Then the URL should match "/admin/report/\d+/checklist#furtherInformation"
#    And the following fields should have the corresponding values:
#      | report_checklist_furtherInformation | |


