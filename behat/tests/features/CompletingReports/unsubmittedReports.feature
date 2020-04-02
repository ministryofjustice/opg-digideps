@completingReports
Feature: Submitting an un-submitted report
  As a deputy
  When I have had a submitted report returned to me for changes
  So I'm sure I've completed whats been asked of me
  I need a way to confirm to OPG I have checked and actioned the sections that require clarification before re-submitting

  Scenario: Create report and return to deputy with changes
    Given the following court orders exist:
      | client   | deputy    | deputy_type  | report_type        | court_date | completed |
      | 12345678 | DeputyZ    | LAY         | Health and Welfare | 2018-01-30 | true      |
    And I have the "2018" to "2019" report between "DeputyZ" and "12345678"
    Given deputy 'DeputyZ@behat-test.com' submits their completed report
    And the submitted report is returned with the following changes:
      | type | dueDateChoice | incompleteSection    |
      | 103  | 4 weeks       | Any other information |

  Scenario: User cannot re-submit report before confirming they have actioned required changes
    Given I am logged in as 'DeputyZ@behat-test.com' with password 'Abcd1234'
    When I follow "Review report"
    Then I should see "More information needed"
    And the button "#edit-report-button" should be "disabled"
    When I click "confirmReview"
    Then the button "#edit-report-button" should be "disabled"
    And I click on the "edit-report-button" button
    Then I should be on "report/{d+}/review"
