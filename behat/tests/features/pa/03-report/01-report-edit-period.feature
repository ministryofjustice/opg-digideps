Feature: PA report

  Scenario: Setup data
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
#    Given the following organisations exist:
#      | name               | emailIdentifier              | activated |
#      | Metric             | @metric.example              | true      |
#    Given the following users exist:
#      | ndr      | deputyType     | firstName | lastName | email                     | postCode | activated |
#      | disabled | PA             | Emily     | Haines   | e.haines@metric.example   | HA4      | true      |
#      | disabled | PA             | James     | Shaw     | j.shaw@metric.example     | HA4      | true      |
#      | disabled | PA_TEAM_MEMBER | Joshua    | Winstead | j.winstead@metric.example | HA4      | true      |
#    And the following users are in the organisations:
#      | userEmail                 | orgName |
#      | e.haines@metric.example   | Metric  |
#      | j.shaw@metric.example     | Metric  |
#      | j.winstead@metric.example | Metric  |
#    Given the following clients exist and are attached to organisations:
#      | firstName   | lastName     | phone       | address     | address2  | county  | postCode | caseNumber | orgEmailIdentifier |
#      | Tustin      | Mollenhauer  | 01215552222 | 1 Fake Road | Fakeville | Faketon | B4 6HQ   | JD123456   | metric.example     |
    Given the following court orders exist:
      | client   | deputy       | deputy_type  | report_type                                | court_date |
      | 78978978 | EmilyHaines  | PA           | Property and Financial Affairs High Assets | 2018-01-30 |
      | 98798798 | James        | LAY          | Health and Welfare                         | 2018-01-30 |

  Scenario: PA does not see unsubmitted reports in the submitted reports section
    Given I have the "2018" to "2019" report between "EmilyHaines" and "78978978"
    And the report has been unsubmitted
    When I am logged in as "EmilyHaines@behat-test.com" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-78978978" region
    Then I should see "No submitted reports" in the "client-profile-reports" region

  # Logic will evolve differently therefore better to have regression test on this
  Scenario: PA edit 102 report dates
    Given I load the application status from "team-users-complete"
    And I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-02100014" region

    And I click on "edit-report-period"
    # check the form loads teh right value (should match with the behat CSV fixtures)
    Then the following fields should have the corresponding values:
      | report_edit_startDate_day   | 29   |
      | report_edit_startDate_month | 05   |
      | report_edit_startDate_year  | 2016 |
      | report_edit_endDate_day     | 28   |
      | report_edit_endDate_month   | 05   |
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
    And I load the application status from "team-users-complete"

  Scenario: PA admin has access to edit 102 report dates
    Given I load the application status from "team-users-complete"
    And I am logged in as "behat-pa1-team-member@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-02100014" region
    And I click on "edit-report-period"
    Then the response status code should be 200
