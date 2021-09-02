@v2 @v2_reporting @money-in-high-assets
Feature: Money in High Assets

@lay-pfa-high-not-started
  Scenario: A user saves and continues without selecting a valid money in option
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I click save and continue
    Then I should see a select option error

@lay-pfa-high-not-started
  Scenario: A user submits a single item of income form with empty values
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have 'Dividends' to report on
    And I try to submit an empty amount
    Then I should see an empty field error

@lay-pfa-high-not-started
  Scenario: A user submits a single item of income form with invalid values
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have 'Dividends' to report on
    And I try to submit an invalid amount
    Then I should see an invalid field error

@lay-pfa-high-not-started
  Scenario: A user submits a two items of income form with valid values
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have 'Dividends' to report on
    And I enter a valid amount
    And I add another item
    And I have 'Dividends' to report on
    And I enter a valid amount
    And I dont add another item
    Then the money in summary page should contain the money in values I added
    When I follow link back to report overview page
    Then I should see "money-in" as "2 items"

@lay-pfa-high-not-started
  Scenario: A user submits a single item of income form with valid values and then edits it
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have 'Dividends' to report on
    And I enter a valid amount
    And I dont add another item
    Then the money in summary page should contain the money in values I added
    When I edit the money in value
    Then the money in summary page should contain the edited value
    When I follow link back to report overview page
    Then I should see "money-in" as "1 item"

@lay-pfa-high-not-started
  Scenario: A user adds a single item of income form with valid values from the summary page
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have 'Dividends' to report on
    And I enter a valid amount
    And I dont add another item
    Then the money in summary page should contain the money in values I added
    When I add another single item of income
    And I dont add another item
    Then the money in summary page should contain the added value

@lay-pfa-high-not-started
  Scenario: A user submits multiple items of income forms with valid values
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have 'Dividends' to report on
    And I enter a valid amount
    When I add another item
    And I have a 'State pension' to report on
    And I enter a valid amount
    And I dont add another item
    Then the money in summary page should contain the money in values I added

@lay-pfa-high-not-started
  Scenario: A user submits a single item of income form with valid values then removes the item
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    And I have 'Dividends' to report on
    And I enter a valid amount
    And I dont add another item
    And I remove the dividends item
    Then I should be on the money in summary page and see entry deleted
    When I follow link back to report overview page
    Then I should see "money-in" as "not started"
