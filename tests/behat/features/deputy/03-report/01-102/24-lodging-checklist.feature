Feature: Admin report checklist

  @deputy
  Scenario: Case manager submits empty checklist for the report 102
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    # Navigate to checklist via search
    And I click on "admin-client-search"
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
    # Begin scenario
    And I click on "checklist" in the "report-2016" region
    Then the URL should match "/admin/report/\d+/checklist"
    And each text should be present in the corresponding region:
      | Not saved yet | last-saved-by |
      | Not saved yet | last-modified-by |
      | 1 Jan 2016 | court-date |
      | Property and affairs: general | report-type-title |
      | 1 Jan 2018 to 31 Dec 2018 | expected-date |
      | Cly | checklist-client-firstname |
      | Hent | checklist-client-lastname |
      | 0123456789 | checklist-client-phone        |
      | dd1-changed | decision-1         |
      | Andy Whites | contact-n2-aw2     |
      | December 2015 | care-plan-last-reviewed |
      | Yes           | has-assets              |
      | Impressionist painting | asset-impressionist-painting |
      | 13 gold house, mortimer road, westminster, SW11 6TF | property-sw11-6tf-address    |
      | 163,010                                             | asset-total                  |
      | £3.00                                               | debt-loans                   |
      | 200 per month payment plan                          | debt-management-details      |
      | £335.40   | checklist-accounts-opening-total |
      | £243.39   | calculated-balance               |
      | £193.11   | balance-difference               |
      | Cly               | checklist-client-firstname |
      | Hent              | checklist-client-lastname |
      | 1 South Parade    | checklist-client-address   |
      | 0123456789        | checklist-client-phone     |
      | John              | checklist-deputy-firstname |
      | Doe               | checklist-deputy-lastname |
      | 102 Petty France  | checklist-deputy-address   |
      | 020 3334 3555     | checklist-deputy-phone     |
      | behat-user@publicguardian.gov.uk | checklist-deputy-email |
    And I should see the "checklist-no-previous-reports" region exactly "1" times
    # check auto-filled answers
    And the following fields should have the corresponding values:
      | report_checklist_futureSignificantDecisions_0 | yes     |
      | report_checklist_hasDeputyRaisedConcerns_0             | yes     |
    When I click on "submit-and-download"
    Then the following fields should have an error:
      | report_checklist_reportingPeriodAccurate_0             |
      | report_checklist_reportingPeriodAccurate_1             |
      | report_checklist_contactDetailsUptoDate                |
      | report_checklist_deputyFullNameAccurateInCasrec        |
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
      | report_checklist_bondAdequate_0        |
      | report_checklist_bondAdequate_1        |
      | report_checklist_bondAdequate_2        |
      | report_checklist_bondOrderMatchCasrec_0        |
      | report_checklist_bondOrderMatchCasrec_1        |
      | report_checklist_bondOrderMatchCasrec_2        |
      | report_checklist_caseWorkerSatisified_0        |
      | report_checklist_caseWorkerSatisified_1        |
      | report_checklist_finalDecision_0        |
      | report_checklist_finalDecision_1        |
      | report_checklist_finalDecision_2        |
      | report_checklist_finalDecision_3        |
      | report_checklist_lodgingSummary         |
    And the URL should match "/admin/report/\d+/checklist"

  @deputy
  Scenario: Case manager saves further information on checklist
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search, client-detail-behat001"
    And I click on "checklist" in the "report-2016" region
    Then the URL should match "/admin/report/\d+/checklist"
    And each text should be present in the corresponding region:
      | Not saved yet | last-saved-by |
    # Begin scenario
    When I fill in "report_checklist_furtherInformationReceived" with "Some more info 1"
    When I click on "save-further-information"
    Then the form should be valid
    Then the URL should match "/admin/report/\d+/checklist#furtherInformation"
    # Assert furtherInfo fields has been empties
    And the following fields should have the corresponding values:
      | report_checklist_furtherInformationReceived |  |
    # Assert furtherInfo table is populated
    And each text should be present in the corresponding region:
      | Case Manager1, Case Manager | last-saved-by            |
      | Some more info 1            | information-1            |
      | Case Manager1, Case Manager | information-created-by-1 |
    Then the URL should match "/admin/report/\d+/checklist"
    And I fill in "report_checklist_furtherInformationReceived" with "Some more info 2"
    When I click on "save-further-information"
    Then the form should be valid
    Then the URL should match "/admin/report/\d+/checklist#furtherInformation"
    # Assert furtherInfo table is updated NOTE reverse order as most recent first.
    And each text should be present in the corresponding region:
      | Some more info 2            | information-1            |
      | Case Manager1, Case Manager | information-created-by-1 |
      | Some more info 1            | information-2            |
      | Case Manager1, Case Manager | information-created-by-2 |
    Then the URL should match "/admin/report/\d+/checklist"


  @deputy @shauns
  Scenario: Admin completes checklist
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    # Navigate to checklist via search
    When I click on "admin-client-search, client-detail-behat001"
    And I click on "checklist" in the "report-2016" region
    Then the URL should match "/admin/report/\d+/checklist"
    And each text should be present in the corresponding region:
      | Case Manager1, Case Manager | last-saved-by |
    # Begin scenario
    When I fill in "report_checklist_reportingPeriodAccurate_0" with "yes"
    And I fill in "report_checklist_contactDetailsUptoDate" with "1"
    And I fill in "report_checklist_deputyFullNameAccurateInCasrec" with "1"
    And I fill in "report_checklist_decisionsSatisfactory_1" with "no"
    And I fill in "report_checklist_consultationsSatisfactory_0" with "yes"
    And I fill in "report_checklist_careArrangements_1" with "no"
    And I fill in "report_checklist_assetsDeclaredAndManaged_2" with "na"
    And I fill in "report_checklist_debtsManaged_0" with "yes"
    And I fill in "report_checklist_openClosingBalancesMatch_1" with "no"
    And I fill in "report_checklist_accountsBalance_2" with "na"
    When I click on "back-to-money-in-out"
    Then the URL should match "/admin/report/\d+/checklist#moneyInOut"
    And I fill in "report_checklist_moneyMovementsAcceptable_0" with "yes"
    And I fill in "report_checklist_bondAdequate_1" with "no"
    And I fill in "report_checklist_bondOrderMatchCasrec_2" with "na"
    And I fill in "report_checklist_futureSignificantDecisions_0" with "yes"
    And I fill in "report_checklist_hasDeputyRaisedConcerns_1" with "no"
    And I fill in "report_checklist_caseWorkerSatisified_0" with "yes"
    And I fill in "report_checklist_finalDecision_0" with "for-review"
    And I fill in "report_checklist_lodgingSummary" with "I am not satisfied"
    Then I click on "save-progress"
    And the response status code should be 200
    And the URL should match "/admin/report/\d+/checklist"
    And each text should be present in the corresponding region:
      | Admin User, OPG Admin | last-saved-by |
    # Assert form reloads with fields saved
    Then the following fields should have the corresponding values:
      | report_checklist_reportingPeriodAccurate_0             | yes                |
      | report_checklist_contactDetailsUptoDate                | 1                  |
      | report_checklist_deputyFullNameAccurateInCasrec        | 1                  |
      | report_checklist_decisionsSatisfactory_1               | no                 |
      | report_checklist_consultationsSatisfactory_0           | yes                |
      | report_checklist_careArrangements_1                    | no                 |
      | report_checklist_assetsDeclaredAndManaged_2            | na                 |
      | report_checklist_debtsManaged_0                        | yes                |
      | report_checklist_openClosingBalancesMatch_1            | no                 |
      | report_checklist_accountsBalance_2                     | na                 |
      | report_checklist_moneyMovementsAcceptable_0            | yes                |
      | report_checklist_bondAdequate_1                        | no                 |
      | report_checklist_bondOrderMatchCasrec_2                | na                 |
      | report_checklist_futureSignificantDecisions_0 | yes                |
      | report_checklist_hasDeputyRaisedConcerns_1             | no                 |
      | report_checklist_caseWorkerSatisified_0                | yes                 |
      | report_checklist_finalDecision_0                       | for-review         |
      | report_checklist_lodgingSummary                        | I am not satisfied |
    Then I click on "submit-and-download"
    And the form should be valid

  @deputy
  Scenario: Admin marked as submitted
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    # Navigate to checklist via search
    And I click on "admin-client-search, client-detail-behat001"
    And I click on "checklist" in the "report-2016" region
    Then the URL should match "/admin/report/\d+/checklist"
    And each text should be present in the corresponding region:
      | Admin User, OPG Admin | last-saved-by     |
      | Admin User, OPG Admin | last-submitted-by |
