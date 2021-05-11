Feature: Managing reports
  In order to ensure that deputies can submit the correct report
  As a case manager
  I need the ability to make certain changes to a report

  Scenario: Create court orders for the feature
    Given the following court orders exist:
      | client   | deputy  | deputy_type | report_type                                | court_date |
      | 95463425 | DeputyX | LAY         | Property and Financial Affairs High Assets | 2018-01-30 |
      | 95432490 | DeputyY | PA          | Health and Welfare                         | 2018-01-30 |
      | 95432265 | DeputyZ | PROF        | Property and Financial Affairs High Assets | 2018-01-30 |
      | 43451678 | DeputyA | LAY         | Health and Welfare                         | 2018-01-30 |
      | 43956843 | DeputyB | LAY         | Health and Welfare                         | 2018-01-30 |

  Scenario Outline: Changing the due date on a report to a relative date
    Given I have the "2018" to "2019" report between "DeputyX" and "95463425"
    When a case manager changes the due date on the report to <adjustment> later
    Then the due date on the report should be <adjustment> from now
    Examples:
      | adjustment |
      | 3 weeks    |
      | 4 weeks    |
      | 5 weeks    |

  Scenario: Changing the due date on a report to a custom date
    Given I have the "2018" to "2019" report between "DeputyX" and "95463425"
    When a case manager changes the due date on the report to "2020-03-20"
    Then the due date on the report should be "2020-03-20"

  Scenario: Changing the report type on a lay report
    Given I have the "2018" to "2019" report between "DeputyX" and "95463425"
    And the "debts" section on the report has been completed
    When a case manager changes the report type on the active report to "103"
    Then the report should have the "103" sections
    And the "debts" section on the report should be completed

  Scenario: Changing the report type on a pa report
    Given I have the "2018" to "2019" report between "DeputyY" and "95432490"
    And the "decisions" section on the report has been completed
    When a case manager changes the report type on the active report to "103-6"
    Then the report should have the "103-6" sections
    And the "decisions" section on the report should be completed

  Scenario: Changing the report type on a prof report
    Given I have the "2018" to "2019" report between "DeputyZ" and "95432265"
    And the "debts" section on the report has been completed
    When a case manager changes the report type on the active report to "103-5"
    Then the report should have the "103-5" sections
    And the "debts" section on the report should be completed

  Scenario: Changing the report type on a submitted report
    Given I have the "2018" to "2019" report between "DeputyA" and "43451678"
    And the report has been submitted
    When a case manager changes the report type on the submitted report to "103"
    Then the report should be unsubmitted
    And the report should have the "103" sections
    And the "decisions" section on the report should be completed

  Scenario: Case manager edit their inputs when managing an active report
    Given I have the "2018" to "2019" report between "DeputyX" and "95463425"
    When a case manager proposes to make the following changes to the report:
      | type | dueDateChoice |
      | 102  | 3 weeks       |
    And I follow "Edit"
    Then the "manage_report[type]" field should contain "102"
    Then the "manage_report[dueDateChoice]" field should contain "3"
    When a case manager proposes to make the following changes to the report:
      | type | dueDateChoice |
      | 103  | 4 weeks       |
    And I follow "Edit"
    Then the "manage_report[type]" field should contain "103"
    Then the "manage_report[dueDateChoice]" field should contain "4"

  Scenario: Case manager edits their inputs when managing a submitting report
    Given I have the "2018" to "2019" report between "DeputyB" and "43956843"
    And the report has been submitted
    When a case manager proposes to make the following changes to the report:
      | type | dueDateChoice | incompleteSection     | startDate  | endDate    |
      | 102  | 3 weeks       | Any other information | 23-04-1994 | 22-04-1995 |
    And I follow "Edit"
    Then the "manage_report[type]" field should contain "102"
    And the "manage_report[dueDateChoice]" field should contain "3"
    And the "Any other information" checkbox is checked
    And the "manage_report_startDate_day" field should contain "23"
    And the "manage_report_startDate_month" field should contain "04"
    And the "manage_report_startDate_year" field should contain "1994"
    And the "manage_report_endDate_day" field should contain "22"
    And the "manage_report_endDate_month" field should contain "04"
    And the "manage_report_endDate_year" field should contain "1995"
    When a case manager proposes to make the following changes to the report:
      | type | dueDateChoice | incompleteSection     | startDate  | endDate    |
      | 103  | 4 weeks       | Visits and care       | 26-03-1998 | 25-02-1999 |
    And I follow "Edit"
    Then the "manage_report[type]" field should contain "103"
    And the "manage_report[dueDateChoice]" field should contain "4"
    And the "Visits and care" checkbox is checked
    And the "manage_report_startDate_day" field should contain "26"
    And the "manage_report_startDate_month" field should contain "03"
    And the "manage_report_startDate_year" field should contain "1998"
    And the "manage_report_endDate_day" field should contain "25"
    And the "manage_report_endDate_month" field should contain "02"
    And the "manage_report_endDate_year" field should contain "1999"
