Feature: Admin unsubmit and client re-submit

  @deputy
  Scenario: Admin unsubmits report for client 02100014
    Given I load the application status from "pa-report-submitted"
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I visit the client page for "02100014"
    Then I should see the "report-2016-to-2017" region in the "report-group-submitted" region
    And I click on "manage" in the "report-2016-to-2017" region
    # unsubmit decisions, PA deputy expenses
    When I fill in the following:
      | manage_report_unsubmittedSection_0_present  | 1    |
      | manage_report_unsubmittedSection_13_present | 1    |
      | manage_report_dueDateChoice_0               | keep |
    And I press "manage_report_save"
    And I fill in "manage_report_confirm_confirm_0" with "yes"
    And I press "manage_report_confirm_save"
    Then I should see the "report-2016-to-2017" region in the "report-group-incomplete" region

  @deputy
  Scenario: PA resubmit report
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "DigidepsPass1234"
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
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I visit the client page for "02100014"
    Then I should see the "report-2016-to-2017" region in the "report-group-submitted" region
    # restore previous status
