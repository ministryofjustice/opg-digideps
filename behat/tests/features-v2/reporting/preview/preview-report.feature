Feature: Preview a summary of the report
  In order to feel confident that the report is correct before I submit it
  As a deputy
  I want to view a summarised preview of the report at any time

  Scenario: Create court orders for the feature
    Given the following court orders exist:
      | client   | deputy    | deputy_type | report_type                                | court_date |
      | 48520098 | Deputy127 | LAY         | Property and Financial Affairs High Assets | 2018-01-30 |

  Scenario: Accessing the report preview for an active report
    Given I am logged in as "deputy127@behat-test.com" with password "DigidepsPass1234"
    And I am viewing the "2018" to "2019" report for "48520098"
    When I follow "edit-report-preview"
    Then the response status code should be 200

  Scenario: Accessing the report preview for an unsubmitted report
    Given I have the "2018" to "2019" report between "Deputy127" and "48520098"
    And the report has been submitted
    And a case manager makes the following changes to the report:
      | incompleteSection     |
      | Any other information |
    When I am logged in as "deputy127@behat-test.com" with password "DigidepsPass1234"
    And I am viewing the "2018" to "2019" report for "48520098"
    When I check "confirmReview"
    And I follow "edit-report-review"
    Then the response status code should be 200
