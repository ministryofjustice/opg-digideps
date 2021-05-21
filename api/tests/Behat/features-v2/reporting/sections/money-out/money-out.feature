@v2 @money-out
Feature: Money Out

#  Scenario: A user attempts to not enter any payments
#    Given a Lay Deputy has not started a Pfa High Assets report
#    And I visit the report overview page
#    Then I should see "money-out" as "not started"
#    When I view and start the money out report section
#    And I try to save and continue without adding a payment
#    Then I should see correct money out validation message

  Scenario: A user adds one of each payment type
    Given a Lay Deputy has not started a Pfa High Assets report
    When I view and start the money out report section
    And I add one of each type of money out payment
    Then I should see the expected results on money out summary page
    When I follow link back to report overview page
    Then I should see "money-out" as "39 items"

  Scenario: A user removes a one off payment
    Given a Lay Deputy has completed a Pfa High Assets report
    When I remove an existing money out payment
    Then I should see the the expected results on money out page
    Then I should see the expected money out section summary

#  Scenario: A user edits a one off payment
#    Given a Lay Deputy has completed a Pfa Low Assets report
#    When I edit an existing money out payment
#    Then I should see the expected money out section summary
#
#  Scenario: A user adds an additional one off payment
#    Given a Lay Deputy has completed a Pfa Low Assets report
#    When I add a payment and state no further payments
#    When I change my mind and add another payment
#    Then I should see the expected money out section summary
#
#  Scenario: A user tries to add a one off payment of less than £1k
#    Given a Lay Deputy has completed a Pfa Low Assets report
#    When I add a one off payment of less than £1k
#    Then I should see correct validation message
