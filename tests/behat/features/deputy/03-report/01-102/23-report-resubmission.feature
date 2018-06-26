Feature: Admin unsubmit report (from client page)

  @deputy
  Scenario: Admin client page + search
    Given I load the application status from "more-documents-added"
    And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
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
  Scenario: Admin unsubmits report and changes report due date and reporting period
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I go to the URL previously saved as "admin-client-search-client-behat001"
    # reports page
    Then the URL should match "/admin/client/\d+/details"
    Then I should see "SUBMITTED" in the "report-2016-label" region
    # assert active report is not lsited
    But I should not see the "report-2017" region
    And I should see "25 February 2017" in the "report-2016-due-date" region
    When I save the application status into "report-2016-pre-unsubmission"
    And I click on "manage" in the "report-2016" region
    # unsubmit with custom due date
    When I fill in the following:
      | unsubmit_report_unsubmittedSection_0_present  | 1      |
      | unsubmit_report_unsubmittedSection_13_present | 1      |
      | unsubmit_report_dueDateChoice_4               | custom |
      | unsubmit_report_dueDateCustom_day             |        |
      | unsubmit_report_dueDateCustom_month           |        |
      | unsubmit_report_dueDateCustom_year            |        |
      | unsubmit_report_startDate_day                 |        |
      | unsubmit_report_startDate_month               |        |
      | unsubmit_report_startDate_year                |        |
      | unsubmit_report_endDate_day                 |        |
      | unsubmit_report_endDate_month               |        |
      | unsubmit_report_endDate_year                |        |
    And I press "unsubmit_report_save"
    Then the following fields should have an error:
      | unsubmit_report_dueDateCustom_day   |
      | unsubmit_report_dueDateCustom_month |
      | unsubmit_report_dueDateCustom_year  |
      | unsubmit_report_startDate_day       |
      | unsubmit_report_startDate_month     |
      | unsubmit_report_startDate_year      |
      | unsubmit_report_endDate_day       |
      | unsubmit_report_endDate_month     |
      | unsubmit_report_endDate_year      |
    # custom date: set to 30th of April 2022 (has to be in the future to skip the constraint)
    When I fill in the following:
      | unsubmit_report_unsubmittedSection_0_present  | 1      |
      | unsubmit_report_unsubmittedSection_13_present | 1      |
      | unsubmit_report_dueDateChoice_4               | custom |
      | unsubmit_report_dueDateCustom_day                   | 30     |
      | unsubmit_report_dueDateCustom_month                 | 04     |
      | unsubmit_report_dueDateCustom_year                  | 2022   |
      | unsubmit_report_startDate_day                 |  02      |
      | unsubmit_report_startDate_month               |  03      |
      | unsubmit_report_startDate_year                |  2016    |
      | unsubmit_report_endDate_day                 |  30      |
      | unsubmit_report_endDate_month               |  11      |
      | unsubmit_report_endDate_year                |  2016    |
    And I press "unsubmit_report_save"
    Then I should see "Unsubmitted" in the "report-2016-label" region
    And I should see "30 April 2022" in the "report-2016-due-date" region
    When I click on "admin-documents"
    Then I should see the "report-submission" region exactly 2 times

  @deputy
  Scenario: Deputy resubmit report
    And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should see "30 April 2022" in the "report-unsubmitted" region
    And I should see the "report-active" region
    But I should not see the "submitted-reports" region
    When I click on "report-start" in the "report-unsubmitted" region
    And I should see the "report-ready-banner" region
    And I should see the "section-decisions-needs-attention" region
    And I should see the "section-deputyExpenses-needs-attention" region
    When I click on "edit-report-review"
#    When I press "report_resubmit_save"
#    Then the following fields should have an error:
#      | report_resubmit_agree |
#    When I check "report_resubmit_agree"
#    And I press "report_resubmit_save"
    And I click on "declaration-page"
    Then the following fields should have the corresponding values:
      | report_declaration_agreedBehalfDeputy_0 | only_deputy |
      | report_declaration_agree                |             |
    When I fill in the following:
      | report_declaration_agree                         | 1           |
      | report_declaration_agreedBehalfDeputy_0          | only_deputy |
      | report_declaration_agreedBehalfDeputyExplanation |             |
    And I press "report_declaration_save"
    Then the form should be valid
    And the URL should match "/report/\d+/submitted"
    # check unsubmitted report disappeared from dashboard
    When I click on "reports"
    Then I should see the "report-active" region
    And I should see the "submitted-reports" region
    But I should not see the "report-unsubmitted" region


  @deputy
  Scenario: admin sees new submission and client page updated
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    # check report being resubmitted
    And I go to the URL previously saved as "admin-client-search-client-behat001"
    Then I should see "SUBMITTED" in the "report-2016-label" region
    # check there is a new submission, with all the documents
    When I click on "admin-documents"
    Then I should see the "report-submission" region exactly 3 times




