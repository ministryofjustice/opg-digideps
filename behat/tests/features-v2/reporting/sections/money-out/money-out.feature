@v2 @money-out
Feature: Money Out

  Scenario: A user has had no money go out
    Given a Lay Deputy has not started a report
    And I visit the report overview page
    Then I should see "money-out-short" as "not started"
    When I view and start the money out report section
    And I have made no payments out
    Then I should see the expected money out section summary
    When I follow link back to report overview page
    Then I should see "money-out-short" as "no items"

#  Scenario: A user has had some money go out but nothing over £1k
#    Given a Lay Deputy has not started a report
#    When I view and start the money out report section
#    And I add some categories of money paid out
#    And I answer that there are no one-off payments over £1k
#    Then I should see the expected money out section summary
#    When I follow link back to report overview page
#    Then I should see "money-out-short" as "no items"
#
#  Scenario: A user has had some money go out including payments over £1k
#    Given a Lay Deputy has not started a report
#    When I view and start the money out report section
#    And I add all the categories of money paid out
#    And I answer that there are a couple of one-off payments over £1k
#    Then I should see the expected money out section summary
#    When I follow link back to report overview page
#    Then I should see "money-out-short" as "2 items"
#
#  Scenario: A user removes a money out payment
#    Given a Lay Deputy has a completed report
#    When I remove an existing money out payment
#    Then I should see the expected money out section summary
#
#  Scenario: A user edits a money out payment
#    Given a Lay Deputy has a completed report
#    When I edit an existing money out payment
#    Then I should see the expected money out section summary
#
#  Scenario: A user changes their mind and removes then re-adds payments
#    Given a Lay Deputy has a completed report
#    And I view the report overview page
#    Then I should see "money-out-short" as "1 item"
#    When I change my mind and remove a payment and all categories
#    Then I should see the expected money out section summary
#    When I change my mind and add a payment
#    Then I should see the expected money out section summary
#    When I follow link back to report overview page
#    Then I should see "money-out-short" as "1 item"
#
#Validation thing...
