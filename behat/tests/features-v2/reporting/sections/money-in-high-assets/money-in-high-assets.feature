@v2 @money-in-high-assets
Feature: Money in High Assets

  Scenario: A user saves and continues without selecting a valid money in option
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    Then I click save and continue
    Then I should see a select option error

  Scenario: A user does not enter an amount into the dividends
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    Then I select dividends
    Then I dont enter an amount I see a error

  Scenario: A user enters an invalid amount into the dividends
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    Then I select dividends
    Then I enter an invalid amount I see a error

  Scenario: A user enters an valid amount into the dividends and see summary page
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    Then I select dividends
    When I enter a valid amount
    Then I dont add another item
    Then I should be on the summary page

  Scenario: A user enters an valid amount into the dividends and then adds another item
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    Then I select dividends
    When I enter a valid amount
    Then I add another item
    Then I select state pension
    When I enter a valid amount
    Then I dont add another item
    Then I should be on the summary page

  Scenario: A user enters an valid amount into the dividends and then removes the item
    Given a Lay Deputy has not started a report
    When I view and start the money in report section
    Then I select dividends
    When I enter a valid amount
    Then I dont add another item
    Then I remove the dividends item
    Then I should be on the money in page and see entry deleted