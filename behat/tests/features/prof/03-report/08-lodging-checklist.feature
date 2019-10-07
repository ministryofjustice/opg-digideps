Feature: Admin report checklist

  Scenario: Case manager submits empty Prof checklist for the report
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search, client-detail-01000010"
    And I click on "checklist" in the "report-2016-to-2017" region
    Then the URL should match "/admin/report/\d+/checklist"
    And I should see the "court-date" region
    And I should see the "expected-date" region
    And each text should be present in the corresponding region:
      | CLY1 HENT1        | fullName                         |
      | 01000010          | case-number                      |
      | Property and affairs: general | report-type-title    |
      | Not saved yet     | last-saved-by                    |
      | Not saved yet     | last-modified-by                 |
      | 20 Mar 2016       | court-date                       |
      | 20 Mar 2018 to 19 Mar 2019 | expected-date           |
      | 20 Mar 2016 - 19 Mar 2017  | submitted-date          |
      | CLY1              | checklist-client-firstname       |
      | HENT1             | checklist-client-lastname        |
      | B301QL            | checklist-client-address         |
      | 078912345678      | checklist-client-phone           |
      | John Named        | checklist-deputy-firstname       |
      | Green             | checklist-deputy-lastname        |
      | 10000000001       | checklist-deputy-phone           |
      | behat-prof1@publicguardian.gov.uk | checklist-deputy-email |
      | rfnd              | reason-no-decisions              |
      | rfnc              | reason-no-contacts               |
      | December 2015     | care-plan-last-reviewed          |
      | No                | has-assets                       |
      | No                | has-debts                        |
      | £100.40           | checklist-accounts-opening-total |
      | £-375.10          | calculated-balance               |
      | £475.5            | balance-difference               |
    # check auto-filled answers
    And the following fields should have the corresponding values:
      | report_checklist_futureSignificantDecisions_0 | yes     |
      | report_checklist_hasDeputyRaisedConcerns_0             | yes     |
    When I click on "submit-and-download"
    Then the following fields should have an error:
      | report_checklist_deputyFullNameAccurateInCasrec        |
      | report_checklist_reportingPeriodAccurate_0             |
      | report_checklist_reportingPeriodAccurate_1             |
      | report_checklist_contactDetailsUptoDate                |
      | report_checklist_decisionsSatisfactory_0               |
      | report_checklist_decisionsSatisfactory_1               |
      | report_checklist_consultationsSatisfactory_0           |
      | report_checklist_consultationsSatisfactory_1           |
      | report_checklist_careArrangements_0                    |
      | report_checklist_careArrangements_1                    |
      | report_checklist_assetsDeclaredAndManaged_0            |
      | report_checklist_assetsDeclaredAndManaged_1            |
      | report_checklist_assetsDeclaredAndManaged_2            |
      | report_checklist_debtsManaged_0                        |
      | report_checklist_debtsManaged_1                        |
      | report_checklist_debtsManaged_2                        |
      | report_checklist_openClosingBalancesMatch_0            |
      | report_checklist_openClosingBalancesMatch_1            |
      | report_checklist_openClosingBalancesMatch_2            |
      | report_checklist_accountsBalance_0                     |
      | report_checklist_accountsBalance_1                     |
      | report_checklist_accountsBalance_2                     |
      | report_checklist_moneyMovementsAcceptable_0            |
      | report_checklist_moneyMovementsAcceptable_1            |
      | report_checklist_bondAdequate_0                        |
      | report_checklist_bondAdequate_1                        |
      | report_checklist_bondAdequate_2                        |
      | report_checklist_bondOrderMatchCasrec_0                |
      | report_checklist_bondOrderMatchCasrec_1                |
      | report_checklist_bondOrderMatchCasrec_2                |
      | report_checklist_paymentsMatchCostCertificate_0        |
      | report_checklist_paymentsMatchCostCertificate_1        |
      | report_checklist_profCostsReasonableAndProportionate_0 |
      | report_checklist_profCostsReasonableAndProportionate_1 |
      | report_checklist_hasDeputyOverchargedFromPreviousEstimates_0 |
      | report_checklist_hasDeputyOverchargedFromPreviousEstimates_1 |
      | report_checklist_hasDeputyOverchargedFromPreviousEstimates_2 |
      | report_checklist_nextBillingEstimatesSatisfactory_0    |
      | report_checklist_nextBillingEstimatesSatisfactory_1    |
      | report_checklist_caseWorkerSatisified_0                |
      | report_checklist_caseWorkerSatisified_1                |
      | report_checklist_finalDecision_0                       |
      | report_checklist_finalDecision_1                       |
      | report_checklist_finalDecision_2                       |
      | report_checklist_finalDecision_3                       |
      | report_checklist_lodgingSummary                        |
    And the URL should match "/admin/report/\d+/checklist"

  Scenario: Case manager saves further information on Prof checklist
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search, client-detail-01000010"
    And I click on "checklist" in the "report-2016-to-2017" region
    Then each text should be present in the corresponding region:
      | Not saved yet | last-saved-by |
    # Begin scenario
    And I fill in "report_checklist_furtherInformationReceived" with "Some more info 1"
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


  Scenario: Admin completes Prof checklist
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search, client-detail-01000010"
    And I click on "checklist" in the "report-2016-to-2017" region
    Then each text should be present in the corresponding region:
      | Case Manager1, Case Manager | last-saved-by |
    # Begin scenario
    And I fill in "report_checklist_deputyFullNameAccurateInCasrec" with "1"
    And I fill in "report_checklist_reportingPeriodAccurate_0" with "yes"
    And I fill in "report_checklist_contactDetailsUptoDate" with "1"
    And I fill in "report_checklist_decisionsSatisfactory_1" with "no"
    And I fill in "report_checklist_consultationsSatisfactory_0" with "yes"
    And I fill in "report_checklist_careArrangements_1" with "no"
    And I fill in "report_checklist_assetsDeclaredAndManaged_2" with "na"
    And I fill in "report_checklist_debtsManaged_0" with "yes"
    And I fill in "report_checklist_openClosingBalancesMatch_1" with "no"
    And I fill in "report_checklist_accountsBalance_2" with "na"
    And I fill in "report_checklist_moneyMovementsAcceptable_0" with "yes"
    And I fill in "report_checklist_bondAdequate_0" with "yes"
    And I fill in "report_checklist_bondOrderMatchCasrec_0" with "yes"
    And I fill in "report_checklist_paymentsMatchCostCertificate_0" with "yes"
    And I fill in "report_checklist_profCostsReasonableAndProportionate_0" with "yes"
    And I fill in "report_checklist_hasDeputyOverchargedFromPreviousEstimates_2" with "na"
    And I fill in "report_checklist_nextBillingEstimatesSatisfactory_1" with "yes"
    And I fill in "report_checklist_futureSignificantDecisions_0" with "yes"
    And I fill in "report_checklist_hasDeputyRaisedConcerns_1" with "no"
    And I fill in "report_checklist_caseWorkerSatisified_0" with "yes"
    And I fill in "report_checklist_finalDecision_0" with "for-review"
    And I fill in "report_checklist_lodgingSummary" with "I am not satisfied"
    Then I click on "save-progress"
    And the response status code should be 200
    And the URL should match "/admin/report/\d+/checklist"
    And each text should be present in the corresponding region:
      | Case Manager1, Case Manager | last-saved-by |
    # Assert form reloads with fields saved
    Then the following fields should have the corresponding values:
      | report_checklist_reportingPeriodAccurate_0             | yes                |
      | report_checklist_contactDetailsUptoDate                | 1                  |
      | report_checklist_decisionsSatisfactory_1               | no                 |
      | report_checklist_consultationsSatisfactory_0           | yes                |
      | report_checklist_careArrangements_1                    | no                 |
      | report_checklist_assetsDeclaredAndManaged_2            | na                 |
      | report_checklist_debtsManaged_0                        | yes                |
      | report_checklist_openClosingBalancesMatch_1            | no                 |
      | report_checklist_accountsBalance_2                     | na                 |
      | report_checklist_moneyMovementsAcceptable_0            | yes                |
      | report_checklist_paymentsMatchCostCertificate_0               | yes                 |
      | report_checklist_profCostsReasonableAndProportionate_0        | yes                 |
      | report_checklist_paymentsMatchCostCertificate_0               | yes                 |
      | report_checklist_hasDeputyOverchargedFromPreviousEstimates_2  | na                  |
      | report_checklist_nextBillingEstimatesSatisfactory_1           | yes                  |
      | report_checklist_futureSignificantDecisions_0          | yes                |
      | report_checklist_hasDeputyRaisedConcerns_1             | no                 |
      | report_checklist_caseWorkerSatisified_0                | yes                |
      | report_checklist_finalDecision_0                       | for-review         |
      | report_checklist_lodgingSummary                        | I am not satisfied |
    Then I click on "submit-and-download"
    And the form should be valid

  Scenario: Admin marked as submitted
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search, client-detail-01000010"
    And I click on "checklist" in the "report-2016-to-2017" region
    Then each text should be present in the corresponding region:
      | Case Manager1, Case Manager | last-saved-by     |
      | Case Manager1, Case Manager | last-submitted-by |
