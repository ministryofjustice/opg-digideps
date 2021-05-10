Feature: Admin unsubmit report (from client page)

  @deputy
  Scenario: Admin unsubmits report and changes report due date and reporting period
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I visit the client page for "102"
    # reports page
    Then the URL should match "/admin/client/\d+/details"
    Then I should see the "report-2016" region in the "report-group-submitted" region
    And I should see "25 February 2017" in the "report-2016-due-date" region
    And I should see "OPG102" in the "report-2016" region
    And I should see the "report-2017" region in the "report-group-active" region
    And I should see "25 February 2018" in the "report-2017-due-date" region
    And I should see "OPG102" in the "report-2017" region
    When I click on "manage" in the "report-2016" region
    # unsubmit with custom due date
    And I fill in the following:
      | manage_report_unsubmittedSection_0_present  | 1      |
      | manage_report_unsubmittedSection_13_present | 1      |
      | manage_report_dueDateChoice_4               | custom |
      | manage_report_dueDateCustom_day             |        |
      | manage_report_dueDateCustom_month           |        |
      | manage_report_dueDateCustom_year            |        |
      | manage_report_startDate_day                 |        |
      | manage_report_startDate_month               |        |
      | manage_report_startDate_year                |        |
      | manage_report_endDate_day                 |        |
      | manage_report_endDate_month               |        |
      | manage_report_endDate_year                |        |
    And I press "manage_report_save"
    Then the following fields should have an error:
      | manage_report_dueDateCustom_day   |
      | manage_report_dueDateCustom_month |
      | manage_report_dueDateCustom_year  |
      | manage_report_startDate_day       |
      | manage_report_startDate_month     |
      | manage_report_startDate_year      |
      | manage_report_endDate_day       |
      | manage_report_endDate_month     |
      | manage_report_endDate_year      |
    # custom date: set to 30th of April 2022 (has to be in the future to skip the constraint)
    When I fill in the following:
      | manage_report_unsubmittedSection_0_present  | 1      |
      | manage_report_unsubmittedSection_13_present | 1      |
      | manage_report_dueDateChoice_4               | custom |
      | manage_report_dueDateCustom_day                   | 30     |
      | manage_report_dueDateCustom_month                 | 04     |
      | manage_report_dueDateCustom_year                  | 2022   |
      | manage_report_startDate_day                 |  02      |
      | manage_report_startDate_month               |  03      |
      | manage_report_startDate_year                |  2016    |
      | manage_report_endDate_day                 |  30      |
      | manage_report_endDate_month               |  11      |
      | manage_report_endDate_year                |  2016    |
    And I press "manage_report_save"
    Then I should see "2 March 2016" in the "report-review" region
    And I should see "Decisions, Deputy expenses" in the "report-review" region
    And I should see "30 April 2022" in the "report-review" region
    When I press "manage_report_confirm_save"
    Then the following fields should have an error:
      | manage_report_confirm_confirm_0   |
      | manage_report_confirm_confirm_1   |
    When I fill in "manage_report_confirm_confirm_0" with "yes"
    And I press "manage_report_confirm_save"
    Then I should see the "report-2016" region in the "report-group-incomplete" region
    And I should see "30 April 2022" in the "report-2016-due-date" region
    When I open the "2016" checklist for client "102"
    And the response status code should be 200


  @deputy
  Scenario: Deputy resubmit report
    Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "DigidepsPass1234"
    Then I should see "30 April 2022" in the "report-unsubmitted" region
    And I should see the "report-active" region
    But I should not see the "submitted-reports" region
    When I click on "report-start" in the "report-unsubmitted" region
    Then I should see "More information needed"
    And I should see the "section-decisions-needs-attention" region
    And I should see the "section-deputyExpenses-needs-attention" region
    When I follow "Preview and check report"
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
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    # check report being resubmitted
    When I visit the client page for "102"
    Then I should see the "report-2016" region in the "report-group-submitted" region
    # check there is a new submission, with all the documents
    When I click on "admin-documents"
    And I click on "tab-pending"
    And I should see "John 102-client"
