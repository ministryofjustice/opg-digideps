Feature: Admin unsubmit and client re-submit

  @deputy
  Scenario: Admin unsubmits report for client 02100014
    Given I load the application status from "pa-report-submitted"
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "admin-client-search"
    When I fill in the following:
      | search_clients_q | 02100014 |
    And I click on "search_clients_search"
    And I click on "client-details" in the "client-02100014" region
    And I save the current URL as "admin-client-02100014.url"
    Then I should see the "report-2016-to-2017" region in the "report-group-done" region
    And I click on "manage" in the "report-2016-to-2017" region
    # unsubmit decisions, PA deputy expenses
    When I fill in the following:
      | unsubmit_report_unsubmittedSection_0_present  | 1    |
      | unsubmit_report_unsubmittedSection_13_present | 1    |
      | unsubmit_report_dueDateChoice_0               | keep |
    And I press "unsubmit_report_save"
    And I fill in "unsubmit_report_confirm_confirm_0" with "yes"
    And I press "unsubmit_report_confirm_save"
    Then I should see the "report-2016-to-2017" region in the "report-group-incomplete" region

  @deputy
  Scenario: PA resubmit report
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    And I fill in "search" with "02100014"
    And I press "search_submit"
    Then I should see the "client" region exactly 2 times
    And I click on "pa-report-open" in the "client-02100014-changes-needed" region
    And I should see "Changes needed" in the "report-detail-status_incomplete" region
    And I should see the "section-decisions-needs-attention" region
    And I should see the "section-paDeputyExpenses-needs-attention" region
    # submit
    When I click on "edit-report_submit_incomplete"
    And I click on "declaration-page"
    And I fill in the following:
      | report_declaration_agree | 1 |
    And I press "report_declaration_save"
    Then the form should be valid

  @deputy
  Scenario: Admin checks report was re-submitted
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I go to the URL previously saved as "admin-client-02100014.url"
    Then I should see the "report-2016-to-2017" region in the "report-group-done" region
    # restore previous status


