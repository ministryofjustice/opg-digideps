@v2 @v2_reporting_2 @money-out-short
Feature: Money Out Short - Org users

    @prof-pfa-low-not-started
    Scenario: A user has had no money go out
        Given a Professional Admin has not started a Pfa Low Assets report
        And I visit the report overview page
        Then I should see "money-out-short" as "not started"
        When I view and start the money out short report section
        And I answer "No" to taking money out on the clients behalf
        And I enter a reason for no money out short
        Then I should see the expected money out section summary
        When I follow link back to report overview page
        Then I should see "money-out-short" as "no money out"

    @prof-pfa-low-not-started
    Scenario: A user has had some money go out but nothing over £1k
        Given a Professional Admin has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add all the categories of money paid out
        And I answer that there are not any one-off payments over £1k
        Then I should see the expected money out section summary
        When I follow link back to report overview page
        Then I should see "money-out-short" as "money out"

    @prof-pfa-low-not-started
    Scenario: A user has had some money go out including payments over £1k
        Given a Professional Admin has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add all the categories of money paid out
        And I answer that there are 4 one-off payments over £1k
        Then I should see the expected money out section summary
        When I follow link back to report overview page
        Then I should see "money-out-short" as "4 items over £1,000"

    @prof-pfa-low-not-started
    Scenario: A user removes a one off payment
        Given a Professional Admin has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add one category of money paid out
        And I answer that there are 2 one-off payments over £1k
        And I remove an existing money out short payment
        Then I should see the expected money out section summary

    @prof-pfa-low-not-started
    Scenario: A user edits a one off payment
        Given a Professional Admin has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add one category of money paid out
        And I answer that there are 2 one-off payments over £1k
        And I edit an existing money out short payment
        Then I should see the expected money out section summary

    @prof-pfa-low-not-started
    Scenario: A user adds an additional one off payment
        Given a Professional Admin has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add a payment and state no further payments
        And I change my mind and add another payment
        Then I should see the expected money out section summary

    @prof-pfa-low-not-started
    Scenario: A user tries to add a one off payment of less than £1k
        Given a Professional Admin has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I answer that there are 1 one-off payments over £1k but add a payment of less than £1K
        Then I should see correct validation message

    @prof-pfa-low-completed
    Scenario: A user edits money out section and adds a one off payment
        Given a Professional Admin has completed a Pfa Low Assets report
        When I edit the money out short section and add a payment
        Then I should see the expected money out section summary

    @prof-pfa-low-completed
    Scenario: A user has had some money go out but nothing over £1k and submits report successfully
        Given a Professional Admin has completed a Pfa Low Assets report
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
        Given I submit the report
        Then my report should be submitted

    @prof-pfa-low-not-started
    Scenario: Transaction items over £1k are restored when user accidentally changes answer to having no one off payments
        Given a Professional Admin has not started a Pfa Low Assets report
        When I view and start the money out short report section
        And I answer "Yes" to taking money out on the clients behalf
        And I add one category of money paid out
        And I answer that there are 3 one-off payments over £1k
        Then I should see the expected money out section summary
        And I edit the money out short "oneOffPaymentsExist" summary section
        And I answer that there are not any one-off payments over £1k
        Then there should be "no" one off payments displayed on the money out summary page
        And I edit the money out short "oneOffPaymentsExist" summary section
        And I answer "yes" to one off payments over £1k for money out
        Then there should be "3" one off payments displayed on the money out summary page
