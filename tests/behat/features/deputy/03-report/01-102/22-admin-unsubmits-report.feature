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
    And I press "unsubmit_report_save"
    Then the URL should match "/admin/report/\d+/change-due-date"
    # empty choice
    When I press "report_change_due_date_save"
    Then the following fields should have an error:
      | report_change_due_date_dueDateChoice_0 |
      | report_change_due_date_dueDateChoice_1 |
      | report_change_due_date_dueDateChoice_2 |
      | report_change_due_date_dueDateChoice_3 |
      | report_change_due_date_dueDateChoice_4 |
    # 4 weeks from now
    And I fill in the following:
      | report_change_due_date_dueDateChoice_2 | 4 |
    When I press "report_change_due_date_save"
    Then the current URL should match with the URL previously saved as "admin-client-search-client-behat001"
    And I should see "Unsubmitted" in the "report-2016-label" region
    And I should see "25 March 2017" in the "report-2016-due-date" region
    # custom due date
    Given I load the application status from "report-2016-pre-unsubmission"
    And I go to the URL previously saved as "admin-client-search-client-behat001"
    And I click on "manage" in the "report-2016" region
    And I press "unsubmit_report_save"
    # custom due date: empty date throws an error
    When I fill in the following:
      | report_change_due_date_dueDateChoice_4 | other |
      | report_change_due_date_dueDate_day     |       |
      | report_change_due_date_dueDate_month   |       |
      | report_change_due_date_dueDate_year    |       |
    And I press "report_change_due_date_save"
    Then the following fields should have an error:
      | report_change_due_date_dueDate_day   |
      | report_change_due_date_dueDate_month |
      | report_change_due_date_dueDate_year  |
    # custom date: set to 30th of April 2022 (has to be in the future to skip the constraint)
    When I fill in the following:
      | report_change_due_date_dueDateChoice_4 | other |
      | report_change_due_date_dueDate_day     | 30    |
      | report_change_due_date_dueDate_month   | 04    |
      | report_change_due_date_dueDate_year    | 2022  |
    And I press "report_change_due_date_save"
    # check data is saved
    And I should see "Unsubmitted" in the "report-2016-label" region
    And I should see "30 April 2022" in the "report-2016-due-date" region

  @deputy
  Scenario: Deputy sees unsunbmitted report
    And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should see "30 April 2022" in the "report-unsubmitted" region

