Feature: Preview a summary of the report
  In order to feel confident that the report is correct before I submit it
  As a deputy
  I want to view a summarised preview of the report at any time

  @mink:browser_stack @chrome @ie11 @android-chrome
  Scenario: Create court orders for the feature
    Given the following court orders exist:
      | client   | deputy    | deputy_type | report_type                                | court_date |
      | 48520098 | Deputy127 | LAY         | Property and Financial Affairs High Assets | 2018-01-30 |
      | 48520099 | Deputy128 | LAY         | Property and Financial Affairs High Assets | 2018-01-30 |
      | 48520100 | Deputy129 | LAY         | Property and Financial Affairs High Assets | 2018-01-30 |

  @mink:browser_stack @chrome
  Scenario: Accessing the report preview for an active report
    Given I am logged in as "deputy127@behat-test.com" with password "DigidepsPass1234"
    And I am viewing the "2018" to "2019" report for "48520098"
    When I click on "edit-report-review"

  @mink:browser_stack @ie11
  Scenario: Accessing the report preview for an active report
    Given I am logged in as "deputy128@behat-test.com" with password "DigidepsPass1234"
    And I am viewing the "2018" to "2019" report for "48520099"
    When I click on "edit-report-review"

  @mink:browser_stack @android-chrome
  Scenario: Accessing the report preview for an active report
    Given I am logged in as "deputy129@behat-test.com" with password "DigidepsPass1234"
    And I am viewing the "2018" to "2019" report for "48520100"
    When I click on "edit-report-review"
