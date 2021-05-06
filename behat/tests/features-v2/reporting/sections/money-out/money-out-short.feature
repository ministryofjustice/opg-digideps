@v2 @money-out
Feature: Money Out

  Scenario: A user has had no money go out
    Given a Lay Deputy has not started a Pfa Low Assets report
    And I visit the report overview page
    Then I should see "money-out-short" as "not started"
    When I view and start the money out report section
    And I have made no payments out
    Then I should see the expected money out section summary
    When I follow link back to report overview page
    Then I should see "money-out-short" as "no items"

  Scenario: A user has had some money go out but nothing over £1k
    Given a Lay Deputy has not started a Pfa Low Assets report
    When I view and start the money out report section
    And I add some categories of money paid out
    And I answer that there are no one-off payments over £1k
    Then I should see the expected money out section summary
    When I follow link back to report overview page
    Then I should see "money-out-short" as "no items"

  Scenario: A user has had some money go out including payments over £1k
    Given a Lay Deputy has not started a Pfa Low Assets report
    When I view and start the money out report section
    And I add all the categories of money paid out
    And I answer that there are a couple of one-off payments over £1k
    Then I should see the expected money out section summary
    When I follow link back to report overview page
    Then I should see "money-out-short" as "2 items"

  Scenario: A user removes a one off payment
    Given a Lay Deputy has completed a Pfa Low Assets report
    When I remove an existing money out payment
    Then I should see the expected money out section summary

  Scenario: A user edits a one off payment
    Given a Lay Deputy has completed a Pfa Low Assets report
    When I edit an existing money out payment
    Then I should see the expected money out section summary

  Scenario: A user adds an additional one off payment
    Given a Lay Deputy has completed a Pfa Low Assets report
    When I add a payment and state no further payments
    When I change my mind and add another payment
    Then I should see the expected money out section summary

  Scenario: A user tries to add a one off payment of less than £1k
    Given a Lay Deputy has completed a Pfa Low Assets report
    When I add a one off payment of less than £1k
    Then I should see correct validation message
