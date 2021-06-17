@v2 @money-in-low-assets
Feature: Money in Low Assets

@pfa-low-not-started
  Scenario: A user has had no money go in
    Given a Lay Deputy has not started a Pfa Low Assets report
    And I visit the report overview page
    Then I should see "money-in-short" as "not started"
    When I view and start the money in short report section
    And I have no payments going out
    Then I should see the expected money in section summary
    When I follow link back to report overview page
    Then I should see "money-in-short" as "no items"

@pfa-low-not-started
  Scenario: A user has had a single item of money go in but nothing over £1k
    Given a Lay Deputy has not started a Pfa Low Assets report
    When I view and start the money in short report section
    And I am reporting on 'Salary or wages'
    And I have no one-off payments over £1k
    Then I should see the expected money in section summary
    When I follow link back to report overview page
    Then I should see "money-in-short" as "no items"

@pfa-low-not-started
  Scenario: A user has had a multiple items of money go in but nothing over £1k
    Given a Lay Deputy has not started a Pfa Low Assets report
    When I view and start the money in short report section
    And I am reporting on 'State pension and benefits, Salary or wages'
    And I have no one-off payments over £1k
    Then I should see the expected money in section summary
    When I follow link back to report overview page
    Then I should see "money-in-short" as "no items"

@pfa-low-not-started
  Scenario: A user has had a single item of money go in and payment over £1k
    Given a Lay Deputy has not started a Pfa Low Assets report
    When I view and start the money in short report section
    And I am reporting on 'Salary or wages'
    And I have a single one-off payments over £1k
    Then I should see the expected money in section summary
    When I follow link back to report overview page
    Then I should see "money-in-short" as "1 item"

@pfa-low-completed
  Scenario: A user edits a one off payment
    Given a Lay Deputy has completed a Pfa Low Assets report
    When I edit an existing money in short payment
    Then I should see the expected money in section summary
