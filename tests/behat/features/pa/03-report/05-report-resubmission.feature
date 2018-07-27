Feature: Admin unsubmit report (from client page)

  @deputy
  Scenario: Admin unsubmits report for client 01000014
    Given I load the application status from "pa-report-submitted"
    And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search"
    When I fill in the following:
      | search_clients_q | 01000014 |
    And I click on "search_clients_search"
    And I click on "client-details" in the "client-01000014" region
    Then I should see "SUBMITTED" in the "report-2016-to-2017" region
    And I click on "manage" in the "report-2016-to-2017" region
    # unsubmit decisions, PA deputy expenses
    When I fill in the following:
      | unsubmit_report_unsubmittedSection_0_present  | 1    |
      | unsubmit_report_unsubmittedSection_13_present | 1    |
      | unsubmit_report_dueDateChoice_0               | keep |
#      | unsubmit_report_dueDateCustom_day             | 30     |
#      | unsubmit_report_dueDateCustom_month           | 04     |
#      | unsubmit_report_dueDateCustom_year            | 2022   |
#      | unsubmit_report_startDate_day                 | 02     |
#      | unsubmit_report_startDate_month               | 03     |
#      | unsubmit_report_startDate_year                | 2016   |
#      | unsubmit_report_endDate_day                   | 30     |
#      | unsubmit_report_endDate_month                 | 11     |
#      | unsubmit_report_endDate_year                  | 2016   |
    And I press "unsubmit_report_save"
    Then I should see "Unsubmitted" in the "report-2016-to-2017" region

  @deputy
  Scenario: PA resubmit report
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I fill in "search" with "01000014"
    And I press "search_submit"
    And I should see the "client" region exactly 1 times
#
#    And I click on "tab-ready"
#    And I click on "pa-report-open" in the "client-01000014" region
#    And I should see the "report-active" region
#    But I should not see the "submitted-reports" region
#    When I click on "report-start" in the "report-unsubmitted" region
#    And I should see the "report-ready-banner" region
#    And I should see the "section-decisions-needs-attention" region
#    And I should see the "section-deputyExpenses-needs-attention" region
#    When I click on "edit-report-review"



