Feature: Managing reports
  In order to ensure that deputies can submit their next report
  As a case manager
  I need the ability to close their previously submitted report

  Scenario: Create court orders for the feature
    Given the following court orders exist:
      | client   | deputy   | deputy_type | report_type                                | court_date |
      | 95465932 | Deputy92 | LAY         | Property and Financial Affairs High Assets | 2016-01-30 |
      | 85473219 | Deputy43 | PA          | Health and Welfare                         | 2018-01-30 |

  Scenario: Case manager cannot close a report that has not been unsubmitted by a case manager
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I visit the management page of the "2016" to "2017" report between "Deputy92" and "95465932"
    Then I should not see "Close Report"

  Scenario: Case manager closes a report that has been marked as incomplete by a case manager
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "DigidepsPass1234"
    And  I have the "2018" to "2019" report between "Deputy43" and "85473219"
    And the report has been submitted
    When a case manager makes the following changes to the report:
      | incompleteSection     |
      | Any other information |
    Then I should see the "report-2018-to-2019" region in the "report-group-incomplete" region
    When I visit the management page of the report
    Then I should see "Close Report"
    When a case manager closes the report
    Then I should see the "report-2018-to-2019" region in the "report-group-submitted" region
