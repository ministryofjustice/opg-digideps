Feature: Prof deputy is discharged

  @shaun
  Scenario: Case manager discharges professional deputy from client
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    And I discharge the deputies from case "01000010"
    And I click on "admin-client-search"
    And I should see "" in the "client-01000010-discharged-on" region
    And I click on "client-detail-01000010"
    And I should see "24 Jul 2019" in the "discharged-on" region

  @shaun
  Scenario: Admin completes Prof checklist against a discharged client
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search, client-detail-01000010"
    And I click on "checklist" in the "report-2016-to-2017" region
    Then each text should be present in the corresponding region:
      | Case Manager1, Case Manager | last-saved-by |
      | 24 Jul 2019 | discharged-on |
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
