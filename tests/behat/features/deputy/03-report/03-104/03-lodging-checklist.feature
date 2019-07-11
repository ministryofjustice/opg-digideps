Feature: Admin report checklist

  @deputy
  Scenario: Case manager submits empty checklist for the report 104
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search, client-detail-102"
    And I click on "checklist" in the "report-2016" region
    Then the URL should match "/admin/report/\d+/checklist"
    # check default values
    And each text should be present in the corresponding region:
      | Not saved yet | last-saved-by |
      | Not saved yet | last-modified-by |
      | 1 Nov 2017 | court-date |
      | Health and welfare | report-type-title |
      | 1 Nov 2018 to 31 Oct 2019 | expected-date |
      | dd1-changed | decision-1         |
      | Andy Whites | contact-n2-aw2     |
      | December 2015 | care-plan-last-reviewed |
      | John          | checklist-client-firstname |
      | 102-client    | checklist-client-lastname |
      | Victoria road    | checklist-client-address   |
      | 022222222222222 | checklist-client-phone        |
      | LAY Deputy 104   | checklist-deputy-firstname |
      | User             | checklist-deputy-lastname |
      | Victoria road    | checklist-deputy-address   |
      | 07911111111111   | checklist-deputy-phone     |
    When I click on "submit-and-download"
    Then the following fields should have an error:
      | report_checklist_reportingPeriodAccurate_0        |
      | report_checklist_reportingPeriodAccurate_1        |
      | report_checklist_contactDetailsUptoDate        |
      | report_checklist_deputyFullNameAccurateInCasrec        |
      | report_checklist_decisionsSatisfactory_0        |
      | report_checklist_decisionsSatisfactory_1        |
      | report_checklist_consultationsSatisfactory_0        |
      | report_checklist_consultationsSatisfactory_1        |
      | report_checklist_careArrangements_0        |
      | report_checklist_careArrangements_1        |
      | report_checklist_satisfiedWithHealthAndLifestyle_0 |
      | report_checklist_satisfiedWithHealthAndLifestyle_1 |
      | report_checklist_caseWorkerSatisified_0        |
      | report_checklist_caseWorkerSatisified_1        |
      | report_checklist_finalDecision_0        |
      | report_checklist_finalDecision_1        |
      | report_checklist_finalDecision_2        |
      | report_checklist_finalDecision_3        |
      | report_checklist_lodgingSummary         |
    And the URL should match "/admin/report/\d+/checklist"

  @deputy
  Scenario: Case manager saves further information on 104 checklist
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search, client-detail-102"
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
      | report_checklist_furtherInformationReceived | |
    # Assert furtherInfo table is populated
    And each text should be present in the corresponding region:
      | Case Manager1, Case Manager | last-saved-by |
      | Some more info 1        | information-1 |
      | Case Manager1, Case Manager   | information-created-by-1 |
    Then the URL should match "/admin/report/\d+/checklist"
    And I fill in "report_checklist_furtherInformationReceived" with "Some more info 2"
    When I click on "save-further-information"
    Then the form should be valid
    Then the URL should match "/admin/report/\d+/checklist#furtherInformation"
    # Assert furtherInfo table is updated NOTE reverse order as most recent first.
    And each text should be present in the corresponding region:
      | Some more info 2              | information-1 |
      | Case Manager1, Case Manager   | information-created-by-1 |
      | Some more info 1              | information-2 |
      | Case Manager1, Case Manager   | information-created-by-2 |
    Then the URL should match "/admin/report/\d+/checklist"


  @deputy
  Scenario: Admin completes 104 checklist
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    # Navigate to checklist via search
    When I click on "admin-client-search, client-detail-102"
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
    And I fill in "report_checklist_satisfiedWithHealthAndLifestyle_0" with "yes"
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
      | report_checklist_reportingPeriodAccurate_0   | yes   |
      | report_checklist_contactDetailsUptoDate | 1  |
      | report_checklist_deputyFullNameAccurateInCasrec  | 1 |
      | report_checklist_decisionsSatisfactory_1     | no   |
      | report_checklist_consultationsSatisfactory_0   | yes   |
      | report_checklist_careArrangements_1    | no |
      | report_checklist_satisfiedWithHealthAndLifestyle_0    | yes |
      | report_checklist_futureSignificantDecisions_0    | yes |
      | report_checklist_hasDeputyRaisedConcerns_1    | no |
      | report_checklist_caseWorkerSatisified_0    | yes |
      | report_checklist_finalDecision_0    | for-review |
      | report_checklist_lodgingSummary    | I am not satisfied |
    Then I click on "submit-and-download"
    And the form should be valid

  @deputy
  Scenario: 104 Admin marked as submitted
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    # Navigate to checklist via search
    And I click on "admin-client-search, client-detail-102"
    And I click on "checklist" in the "report-2016" region
    Then the URL should match "/admin/report/\d+/checklist"
    And each text should be present in the corresponding region:
      | Admin User, OPG Admin | last-saved-by     |
      | Admin User, OPG Admin | last-submitted-by |
