Feature: Prof deputy is discharged

  @shaun
  Scenario: Case manager discharges professional deputy from client
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    And I discharge the deputies from case "01000010"
    And I click on "admin-client-search"
    And I should see "" in the "client-01000010-discharged-on" region
    And I click on "client-detail-01000010"
    And I should see "24 Jul 2018" in the "discharged-on" region

  @shaun
  Scenario: Admin completes Prof checklist against a discharged client
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search, client-detail-01000010"
    And I click on "checklist" in the "report-2016-to-2017" region
    Then each text should be present in the corresponding region:
      | Case Manager1, Case Manager | last-saved-by |
      | 24 Jul 2018 | discharged-on |
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

  @shaun
  Scenario: add deputy user from registration page
    Given emails are sent from "deputy" area
    When I am on "/register"
    And I add the following users to CASREC:
      | Case     | Surname | Deputy No | Dep Surname | Dep Postcode | Typeofrep |
      | 01000010 | Hent1   | BEHAT002  | Doe         | P0ST C0D3    | OPG102    |
    And I fill in the following:
      | self_registration_firstname       | John                                 |
      | self_registration_lastname        | Doe                                  |
      | self_registration_email_first     | behat-user2@publicguardian.gov.uk |
      | self_registration_email_second    | behat-user2@publicguardian.gov.uk |
      | self_registration_postcode        | P0ST C0D3                            |
      | self_registration_clientFirstname | Cly                                  |
      | self_registration_clientLastname  | Hent1                                 |
      | self_registration_caseNumber      | 01000010                             |
    And I press "self_registration_save"
    Then I should see "Please check your email"
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-user2@publicguardian.gov.uk"

  @shaun
  Scenario: login and add user (deputy)
    Given emails are sent from "deputy" area
    Given I am on "/logout"
  # follow link
    When I open the "/user/activate/" link from the email
    Then the response status code should be 200
    When I fill in the password fields with "Abcd1234"
    And I check "set_password_showTermsAndConditions"
    And I press "set_password_save"
    Then the form should be valid
    And I should see the "user-details" region
    Then the following hidden fields should have the corresponding values:
      | user_details_firstname       | John      |
      | user_details_lastname        | Doe       |
      | user_details_addressPostcode | P0ST C0D3 |
    And the following fields should have the corresponding values:
      | user_details_address1        |           |
      | user_details_addressCountry  |           |
      | user_details_phoneMain       |           |
    When I set the user details to:
      | address | 102 Petty France | MOJ           | London | PREFILLED | GB |
      | phone   | 020 3334 3555    | 020 1234 5678 |        |           |    |
    Then the form should be valid
    When I go to "/user/details"
    Then the following hidden fields should have the corresponding values:
      | user_details_firstname       | John      |
      | user_details_lastname        | Doe       |
      | user_details_addressPostcode | P0ST C0D3 |
    And the following fields should have the corresponding values:
      | user_details_address1         | 102 Petty France |
      | user_details_address2         | MOJ              |
      | user_details_address3         | London           |
      | user_details_addressCountry   | GB               |
      | user_details_phoneMain        | 020 3334 3555    |
      | user_details_phoneAlternative | 020 1234 5678    |

  @shaun
  Scenario: update client (client name/case number/postcode already set)
    Given I am logged in as "behat-user2@publicguardian.gov.uk" with password "Abcd1234"
    Then I should be on "client/add"
# submit empty form and check errors
    Then the following hidden fields should have the corresponding values:
      | client_firstname  | Cly      |
      | client_lastname   | Hent1     |
      | client_caseNumber | 01000010 |
    And the following fields should have the corresponding values:
      | client_postcode   |  |
      | client_courtDate_day   |  |
      | client_courtDate_month |  |
      | client_courtDate_year  |  |
    When I set the client details to:
      | courtDate  | 25              | 7          | 2018      |         |    |
      # only tick Property and Affairs
      # if  Personal Welfare  is re-enabled, select the other one, then de-comment next feature block (about changing COT)
      | address    | 1 South Parade | First Floor | Nottingham | NG1 2HT | GB |
      | phone      | 0123456789     |             |            |         |    |
    Then the URL should match "report/create/\d+"
    When I go to "client/add"
    Then the following hidden fields should have the corresponding values:
      | client_firstname  | Cly      |
      | client_lastname   | Hent1     |
      | client_caseNumber | 01000010 |
      | client_postcode   | NG1 2HT  |
      | client_courtDate_day   | 25             |
      | client_courtDate_month | 07             |
      | client_courtDate_year  | 2018          |
    And the following fields should have the corresponding values:
      | client_courtDate_day   | 25             |
      | client_courtDate_month | 07             |
      | client_courtDate_year  | 2018           |
      | client_address         | 1 South Parade |
      | client_address2        | First Floor    |
      | client_county          | Nottingham     |
      | client_country         | GB             |
      | client_phone           | 0123456789     |

  @shaun
  Scenario: create report for new lay deputy
    Given I am logged in as "behat-user2@publicguardian.gov.uk" with password "Abcd1234"
    Then the URL should match "report/create/\d+"
    When I fill in the following:
      | report_startDate_day   | 25   |
      | report_startDate_month | 07   |
      | report_startDate_year  | 2018 |
      | report_endDate_day     | 24   |
      | report_endDate_month   | 07   |
      | report_endDate_year    | 2019 |
    And I press "report_save"
    Then the URL should match "/lay"
