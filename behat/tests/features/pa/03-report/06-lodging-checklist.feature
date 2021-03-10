Feature: Admin report checklist


  Scenario: Case manager submits empty PA checklist for the report
    Given I load the application status from "pa-report-submitted"
    And I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I open the "2016-to-2017" checklist for client "02100014"
    Then the URL should match "/admin/report/\d+/checklist"
    And I should see the "court-date" region
    And each text should be present in the corresponding region:
      | Not saved yet     | lodging-last-saved-by                    |
    #failing on master
      | Cly7              | checklist-client-firstname       |
      | Hent              | checklist-client-lastname        |
      | 078912345678      | checklist-client-phone           |
      | rfnd              | reason-no-decisions              |
      | rfnc              | reason-no-contacts               |
      | December 2015     | care-plan-last-reviewed          |
      | No                | has-assets                       |
      | No                | has-debts                        |
      | £100.40           | checklist-accounts-opening-total |
      | £100.40           | calculated-balance               |
      | £0.00             | balance-difference               |
      | Cly7              | checklist-client-firstname       |
      | Hent              | checklist-client-lastname        |
      | B301QL            | checklist-client-address         |
      | 078912345678      | checklist-client-phone           |
      | DEP1              | checklist-deputy-firstname       |
      | SURNAME1          | checklist-deputy-lastname        |
      | +4410000000001    | checklist-deputy-phone           |
      | behat-pa1@publicguardian.gov.uk | checklist-deputy-email |
    # check auto-filled answers
    And the following fields should have the corresponding values:
      | report_checklist_futureSignificantDecisions_0 | yes     |
      | report_checklist_hasDeputyRaisedConcerns_0             | yes     |
    When I click on "submit-and-continue" in the "lodging-checklist" region
    Then the following fields should have an error:
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
      | report_checklist_deputyChargeAllowedByCourt_0          |
      | report_checklist_deputyChargeAllowedByCourt_1          |
      | report_checklist_satisfiedWithPaExpenses_0             |
      | report_checklist_satisfiedWithPaExpenses_1             |
      | report_checklist_satisfiedWithPaExpenses_2             |
      | report_checklist_caseWorkerSatisified_0                |
      | report_checklist_caseWorkerSatisified_1                |
      | report_checklist_finalDecision_0                       |
      | report_checklist_finalDecision_1                       |
      | report_checklist_finalDecision_2                       |
      | report_checklist_finalDecision_3                       |
      | report_checklist_lodgingSummary                        |
    And the URL should match "/admin/report/\d+/checklist"

  Scenario: Case manager saves further information on PA checklist
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I open the "2016-to-2017" checklist for client "02100014"
    Then each text should be present in the corresponding region:
      | Not saved yet | lodging-last-saved-by |
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
      | Case Manager1, Admin | lodging-last-saved-by |
      | Some more info 1            | information           |
      | Case Manager1, Admin | information           |
    Then the URL should match "/admin/report/\d+/checklist"
    And I fill in "report_checklist_furtherInformationReceived" with "Some more info 2"
    When I click on "save-further-information"
    Then the form should be valid
    Then the URL should match "/admin/report/\d+/checklist#furtherInformation"
    And each text should be present in the corresponding region:
      | Some more info 2            | information |
      | Case Manager1, Admin | information |
      | Some more info 1            | information |
    Then the URL should match "/admin/report/\d+/checklist"


  Scenario: Admin completes PA checklist
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I open the "2016-to-2017" checklist for client "02100014"
    Then each text should be present in the corresponding region:
      | Case Manager1, Admin | lodging-last-saved-by |
    # Begin scenario
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
    And I fill in "report_checklist_satisfiedWithPaExpenses_1" with "no"
    And I fill in "report_checklist_deputyChargeAllowedByCourt_0" with "yes"
    And I fill in "report_checklist_futureSignificantDecisions_0" with "yes"
    And I fill in "report_checklist_hasDeputyRaisedConcerns_1" with "no"
    And I fill in "report_checklist_caseWorkerSatisified_0" with "yes"
    And I fill in "report_checklist_finalDecision_0" with "for-review"
    And I fill in "report_checklist_lodgingSummary" with "I am not satisfied"
    Then I click on "save-progress" in the "lodging-checklist" region
    And the response status code should be 200
    And the URL should match "/admin/report/\d+/checklist"
    And each text should be present in the corresponding region:
      | Case Manager1, Admin | lodging-last-saved-by |
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
      | report_checklist_deputyChargeAllowedByCourt_0          | yes                 |
      | report_checklist_satisfiedWithPaExpenses_1             | no                 |
      | report_checklist_futureSignificantDecisions_0 | yes                |
      | report_checklist_hasDeputyRaisedConcerns_1             | no                 |
      | report_checklist_caseWorkerSatisified_0                | yes                 |
      | report_checklist_finalDecision_0                       | for-review         |
      | report_checklist_lodgingSummary                        | I am not satisfied |
    Then I click on "submit-and-continue" in the "lodging-checklist" region
    And the form should be valid

  Scenario: Admin marked as submitted
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I open the "2016-to-2017" checklist for client "02100014"
    Then each text should be present in the corresponding region:
      | Case Manager1, Admin | lodging-last-saved-by     |
      | Case Manager1, Admin | lodging-last-submitted-by |
