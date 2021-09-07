@v2 @v2_reporting_2 @money-out
Feature: Money Out

    @lay-pfa-high-not-started
    Scenario: A user attempts to not enter any payments
        Given a Lay Deputy has not started a Pfa High Assets report
        And I visit the report overview page
        Then I should see "money-out" as "not started"
        When I view and start the money out report section
        And I try to save and continue without adding a payment
        Then I should see correct money out validation message

    @lay-pfa-high-not-started
    Scenario: A user adds one of each payment type
        Given a Lay Deputy has not started a Pfa High Assets report
        When I view and start the money out report section
        And I add one of each type of money out payment
        Then I should see the expected results on money out summary page
        When I follow link back to report overview page
        Then I should see "money-out" as "39 items"

    @lay-pfa-high-not-started
    Scenario: A user removes a payment
        Given a Lay Deputy has not started a Pfa High Assets report
        When I view and start the money out report section
        And I add one money out payment
        When I add another money out payment from an existing account
        When I visit the money out summary section
        And I remove an existing money out payment
        Then I should see the expected results on money out summary page

    @lay-pfa-high-completed
    Scenario: A user edits an existing payment
        Given a Lay Deputy has completed a Pfa High Assets report
        When I visit the money out summary section
        And I edit an existing money out payment
        Then I should see the expected results on money out summary page

    @lay-pfa-high-not-started
    Scenario: A user adds an additional payment
        Given a Lay Deputy has not started a Pfa High Assets report
        When I view and start the money out report section
        And I add one money out payment
        When I add another money out payment from an existing account
        Then I should see the expected results on money out summary page

    @lay-pfa-high-not-started
    Scenario: A user tries to add a one off payment of less than £1k
        Given a Lay Deputy has not started a Pfa High Assets report
        When I view and start the money out report section
        And I add a payment without filling in description and amount
        Then I should see correct money out description and amount validation message
