Feature: PA report

  Scenario: Setup data
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    Given the following court orders exist:
      | client   | deputy           | deputy_type | report_type                                | court_date |
      | 78978978 | EmilyHainesNamed | PA          | Property and Financial Affairs High Assets | 2018-01-30 |
      | 98798798 | JamesShawAdmin   | PA_ADMIN    | High Assets with Health and Welfare        | 2018-01-30 |

    Given the following users are in the organisations:
      | userEmail                       | orgName |
      | EmilyHainesNamed@behat-test.com | PA OPG  |
      | JamesShawAdmin@behat-test.com   | PA OPG  |

  Scenario: PA does not see unsubmitted reports in the submitted reports section
    Given I have the "2018" to "2019" report between "EmilyHainesNamed" and "78978978"
    And the report has been unsubmitted
    When I am logged in as "EmilyHainesNamed@behat-test.com" with password "DigidepsPass1234"
    And I fill in "search" with "78978978"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-78978978" region
    Then I should see "No submitted reports" in the "client-profile-reports" region

  # Logic will evolve differently therefore better to have regression test on this
  Scenario: PA edit 102 report dates
    And I am logged in as "JamesShawAdmin@behat-test.com" with password "DigidepsPass1234"
    And I fill in "search" with "98798798"
    And I press "search_submit"
    When I click on "pa-report-open" in the "client-98798798" region
    And I click on "edit-report-period"
    # check the form loads teh right value (should match with the behat CSV fixtures)
    Then the following fields should have the corresponding values:
      | report_edit_startDate_day   | 30   |
      | report_edit_startDate_month | 01   |
      | report_edit_startDate_year  | 2018 |
      | report_edit_endDate_day     | 29   |
      | report_edit_endDate_month   | 01   |
      | report_edit_endDate_year    | 2019 |
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
