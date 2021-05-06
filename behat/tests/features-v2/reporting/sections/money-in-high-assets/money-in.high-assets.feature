@v2 @money-in-high-assets
Feature: Money in High Assets

  Scenario: A user saves and continues without selecting a valid money in option
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I click save and continue
    Then I should see a select option error

  Scenario: A user submits a single item of income form with empty values
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have a dividend to report on
    And I try to submit an empty amount
    Then I should see an empty field error

  Scenario: A user submits a single item of income form with invalid values
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have a dividend to report on
    And I try to submit an invalid amount
    Then I should see an invalid field error

  Scenario: A user submits a single item of income form with valid values
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have a dividend to report on
    And I enter a valid amount
    And I dont add another item
    Then the money in summary page should contain the money in values I added

  Scenario: A user submits a single item of income form with valid values and then edits it
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have a dividend to report on
    And I enter a valid amount
    And I dont add another item
    Then the money in summary page should contain the money in values I added
    When I edit the money in value
    Then the money in summary page should contain the edited value

  Scenario: A user submits multiple items of income forms with valid values
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have a dividend to report on
    And I enter a valid amount
    When I add another item
    And I select state pension
    And I enter a valid amount
    And I dont add another item
    Then the money in summary page should contain the money in values I added

  Scenario: A user submits a single item of income form with valid values then removes the item
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have a dividend to report on
    And I enter a valid amount
    And I dont add another item
    And I remove the dividends item
    Then I should be on the money in summary page and see entry deleted
