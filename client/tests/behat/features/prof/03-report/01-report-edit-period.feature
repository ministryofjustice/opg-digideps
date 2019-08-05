Feature: PROF report 102-5

  @102-5
  Scenario: PROF does not see unsubmitted reports in the submitted reports section
    Given I load the application status from "prof-team-users-complete"
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-01000010" region
    Then I should see "No submitted reports" in the "client-profile-reports" region


  # Logic will evolve differently therefore better to have regression test on this
  @102-5
  Scenario: PROF edit 102-5 report dates
    Given I load the application status from "prof-team-users-complete"
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-01000010" region

    And I click on "edit-report-period"
    # check the form loads teh right value (should match with the behat CSV fixtures)
    Then the following fields should have the corresponding values:
      | report_edit_startDate_day   | 20   |
      | report_edit_startDate_month | 03   |
      | report_edit_startDate_year  | 2016 |
      | report_edit_endDate_day     | 19   |
      | report_edit_endDate_month   | 03   |
      | report_edit_endDate_year    | 2017 |
    # check validations (from here, same steps as Lay deputy
    When I fill in the following:
      | report_edit_startDate_day   | aa |
      | report_edit_startDate_month | bb |
      | report_edit_startDate_year  | c  |
      | report_edit_endDate_day     |    |
      | report_edit_endDate_month   |    |
      | report_edit_endDate_year    |    |
    And I press "report_edit_save"
    Then the following fields should have an error:
      | report_edit_startDate_day   |
      | report_edit_startDate_month |
      | report_edit_startDate_year  |
      | report_edit_endDate_day     |
      | report_edit_endDate_month   |
      | report_edit_endDate_year    |
        # change the values
    When I fill in the following:
      | report_edit_startDate_day   | 28   |
      | report_edit_startDate_month | 04   |
      | report_edit_startDate_year  | 2015 |
      | report_edit_endDate_day     | 27   |
      | report_edit_endDate_month   | 04   |
      | report_edit_endDate_year    | 2016 |
    And I press "report_edit_save"
    Then the form should be valid
        # check values changed correctly
    When I click on "edit-report-period"
    Then the following fields should have the corresponding values:
      | report_edit_startDate_day   | 28   |
      | report_edit_startDate_month | 04   |
      | report_edit_startDate_year  | 2015 |
      | report_edit_endDate_day     | 27   |
      | report_edit_endDate_month   | 04   |
      | report_edit_endDate_year    | 2016 |
    # restore initial values (for future tests and have this test not affecting subsequent scenarios)
    And I load the application status from "prof-team-users-complete"

  @102-5
  Scenario: PROF admin has access to edit 102-5 report dates
    Given I load the application status from "prof-team-users-complete"
    And I am logged in as "behat-prof1-team-member@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-01000014" region
    And I click on "edit-report-period"
    Then the response status code should be 200
