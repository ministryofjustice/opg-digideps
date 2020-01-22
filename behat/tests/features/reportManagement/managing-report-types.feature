Feature: Managing report types
  In order to ensure that deputies can submit the correct report
  As a case manager
  I need the ability to change the type of each report

  Scenario: Create court orders for the feature
    Given the following court orders exist:
      | client   | deputy  | deputy_type | report_type                                | court_date |
      | 95463425 | DeputyX | LAY         | Property and Financial Affairs High Assets | 2018-01-30 |
      | 95432490 | DeputyY | PA          | Health and Welfare                         | 2018-01-30 |
      | 95432265 | DeputyZ | PROF        | Property and Financial Affairs High Assets | 2018-01-30 |

  Scenario: Changing the report type for an active lay report
    Given the "debts" section for the "2018" to "2019" report between "DeputyX" and "95463425" has been completed
    When a case manager changes the report type for the active "2018" to "2019" report between "DeputyX" and "95463425" to "103"
    Then the "2018" to "2019" report between "DeputyX" and "95463425" should have the "103" sections
    And the "debts" section for the "2018" to "2019" report between "DeputyX" and "95463425" should be completed

  Scenario: Changing the report type for an active organisation based report
    Given the "debts" section for the "2018" to "2019" report between "DeputyZ" and "95432265" has been completed
    When a case manager changes the report type for the active "2018" to "2019" report between "DeputyZ" and "95432265" to "103-5"
    Then the "2018" to "2019" report between "DeputyZ" and "95432265" should have the "103-5" sections
    And the "debts" section for the "2018" to "2019" report between "DeputyZ" and "95432265" should be completed

  Scenario: Changing the report type for a submitted report
    Given the "2018" to "2019" report between "DeputyY" and "95432490" has been submitted:
    When a case manager changes the report type for the submitted "2018" to "2019" report between "DeputyY" and "95432490" to "103-6"
    Then the "2018" to "2019" report between "DeputyY" and "95432490" should have the "103-6" sections



