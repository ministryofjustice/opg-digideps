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
  Scenario: Admin unsubmits report and changes report due date
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I go to the URL previously saved as "admin-client-search-client-behat001"
    # reports page
    Then the URL should match "/admin/client/\d+/details"
    And I should see "SUBMITTED" in the "report-2016-label" region
    And I should see "NOT FINISHED" in the "report-2017-label" region
    And I should see "25 February 2017" in the "report-2016-due-date" region
    And I save the application status into "report-2016-pre-unsubmission"
    When I click on "manage" in the "report-2016" region
    And I save the current URL as "report-2016-unsubmitted"
    And I save the application status into "report-2016-unsubmitted"
    # unsubmit without a section selection
    And I press "unsubmit_report_save"
      | unsubmit_report_dueDateChoice_2 | 4 |
    Then the form should be invalid
    # unsubmit with decisions and deputy expenses, due date 4 weeks from now
    When I fill in the following:
      | unsubmit_report_unsubmittedSection_0_present  | 1 |
      | unsubmit_report_unsubmittedSection_13_present | 1 |
      | unsubmit_report_dueDateChoice_2               | 4 |
    And I press "unsubmit_report_save"
    # check client page
    And I should see "Unsubmitted" in the "report-2016-label" region
    And I should see "25 March 2017" in the "report-2016-due-date" region
    # resubmit with custom due date
    Given I load the application status from "report-2016-unsubmitted"
    And I go to the URL previously saved as "report-2016-unsubmitted"
    And I fill in the following:
      | unsubmit_report_unsubmittedSection_0_present  | 1     |
      | unsubmit_report_unsubmittedSection_13_present | 1     |
      | unsubmit_report_dueDateChoice_4               | other |
      | unsubmit_report_dueDate_day                   |       |
      | unsubmit_report_dueDate_month                 |       |
      | unsubmit_report_dueDate_year                  |       |
    And I press "unsubmit_report_save"
    Then the following fields should have an error:
      | unsubmit_report_dueDate_day   |
      | unsubmit_report_dueDate_month |
      | unsubmit_report_dueDate_year  |
    # custom date: set to 30th of April 2022 (has to be in the future to skip the constraint)
    When I fill in the following:
      | unsubmit_report_unsubmittedSection_0_present  | 1     |
      | unsubmit_report_unsubmittedSection_13_present | 1     |
      | unsubmit_report_dueDateChoice_4               | other |
      | unsubmit_report_dueDate_day                   | 30    |
      | unsubmit_report_dueDate_month                 | 04    |
      | unsubmit_report_dueDate_year                  | 2022  |
    And I press "unsubmit_report_save"
    And I should see "Unsubmitted" in the "report-2016-label" region
    And I should see "30 April 2022" in the "report-2016-due-date" region
#
#    # Due date form: 4 weeks from now
#    And I fill in the following:
#
#    When I press "report_change_due_date_save"
#    Then the current URL should match with the URL previously saved as "admin-client-search-client-behat001"
#    And I should see "Unsubmitted" in the "report-2016-label" region
#    And I should see "25 March 2017" in the "report-2016-due-date" region

#  @deputy
#  Scenario: Admin unsubmits report with custom due date
#    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
#    And I load the application status from "report-2016-unsubmitted"
#    And I go to the URL previously saved as "report-2016-unsubmitted"
#
#
#  @deputy
#  Scenario: Deputy resubmit report
#    And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#    Then I should see "30 April 2022" in the "report-unsubmitted" region
#    When I click on "report-review" in the "report-unsubmitted" region
#    Then I should see the "report-hero-unsubmitted" region
#    Then I should see the "section-decisions-needs-attention" region
#    Then I should see the "section-deputyExpenses-needs-attention" region
#    When I press "report_resubmit_save"
#    Then the following fields should have an error:
#      | report_resubmit_agree |
#    When I check "report_resubmit_agree"
#    And I press "report_resubmit_save"
#    Then the URL should match "/report/\d+/review"
