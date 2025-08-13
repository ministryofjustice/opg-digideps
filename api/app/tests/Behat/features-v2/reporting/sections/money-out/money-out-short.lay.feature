@v2 @v2_reporting_2 @money-out-short
Feature: Money Out Short - Lay users

    @lay-pfa-low-not-started
    Scenario: A user has had no money go out
        Given a Lay Deputy has not started a Pfa Low Assets report
        And I visit the report overview page
        Then I should see "money-out-short" as "not started"
        When I view and start the money out short report section
        And I answer "No" to taking money out on the clients behalf
        And I enter a reason for no money out short
        Then I should see the expected money out section summary
        When I follow link back to report overview page
        Then I should see "money-out-short" as "no money out"

    @lay-pfa-low-not-started
    Scenario: A user has had some money go out but nothing over £1k
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add all the categories of money paid out
        And I answer that there are not any one-off payments over £1k
        Then I should see the expected money out section summary
        When I follow link back to report overview page
        Then I should see "money-out-short" as "money out"

    @lay-pfa-low-not-started
    Scenario: A user has had some money go out including payments over £1k
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add all the categories of money paid out
        And I answer that there are 4 one-off payments over £1k
        Then I should see the expected money out section summary
        When I follow link back to report overview page
        Then I should see "money-out-short" as "4 items over £1,000"

    @lay-pfa-low-not-started
    Scenario: A user removes a one off payment
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add one category of money paid out
        And I answer that there are 2 one-off payments over £1k
        And I remove an existing money out short payment
        Then I should see the expected money out section summary

    @lay-pfa-low-not-started
    Scenario: A user edits a one off payment
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add one category of money paid out
        And I answer that there are 2 one-off payments over £1k
        And I edit an existing money out short payment
        Then I should see the expected money out section summary

    @lay-pfa-low-not-started
    Scenario: A user adds an additional one off payment
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add a payment and state no further payments
        And I change my mind and add another payment
        Then I should see the expected money out section summary

    @lay-pfa-low-not-started
    Scenario: A user tries to add a one off payment of less than £1k
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I answer that there are 1 one-off payments over £1k but add a payment of less than £1K
        Then I should see correct validation message

    @lay-pfa-low-completed
    Scenario: A user edits money out section and adds a one off payment
        Given a Lay Deputy has completed a Pfa Low Assets report
        When I edit the money out short section and add a payment
        Then I should see the expected money out section summary

    @lay-pfa-low-completed
    Scenario: A user has had some money go out but nothing over £1k and submits report successfully
        Given a Lay Deputy has completed a Pfa Low Assets report
        And I visit the report overview page
        Then I should see "money-out-short" as "no money out"
        When I visit the short money out summary section
        And I edit the money out short "exist" summary section
        And I answer "Yes" to taking money out on the clients behalf
        When I add one category of money paid out
        And I answer that there are not any one-off payments over £1k
        Then I should see the expected money out section summary
        When I follow link back to report overview page
        Then I should see "money-out-short" as "money out"
        Then I follow the submission process to the declaration page for "current" report
        And I fill in the declaration page and submit the report
        Then my report should be submitted

    @lay-pfa-low-not-started
    Scenario: Transaction items over £1k are restored when user accidentally changes answer to reporting no money out
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add one category of money paid out
        And I answer that there are 2 one-off payments over £1k
        Then I should see the expected money out section summary
        When I edit the money out short "exist" summary section
        And I answer "No" to taking money out on the clients behalf
        And I enter a reason for no money out short
        Then there should be "no" one off payments displayed on the money out summary page
        When I edit the money out short "exist" summary section
        Then I answer "Yes" to taking money out on the clients behalf
        And I add one category of money paid out
        And I answer "yes" to one off payments over £1k for money out
        Then there should be "2" one off payments displayed on the money out summary page


    @lay-pfa-low-not-started
    Scenario: A user adds a transaction item and then removes it and reports to having no money out then adds a new transaction item
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add one category of money paid out
        And I answer that there are 1 one-off payments over £1k
        Then I should see the expected money out section summary
        When I remove an existing money out short payment
        Then there should be "no" one off payments displayed on the money out summary page
        Then I edit the answer to the money out one off payment over 1k
        And I answer that there are 1 one-off payments over £1k
        Then there should be "1" one off payments displayed on the money out summary page
