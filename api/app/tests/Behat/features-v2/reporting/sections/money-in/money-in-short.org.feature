@v2 @v2_reporting_2 @money-in-low-assets
Feature: Money in Low Assets - Org users

    @prof-pfa-low-not-started
    Scenario: A user has had no money go in
        Given a Professional Admin has not started a Pfa Low Assets report
        And I visit the report overview page
        Then I should see "money-in-short" as "not started"
        When I view and start the money in short report section
        And I confirm "No" to adding money in on the clients behalf
        And I enter a reason for no money in short
        Then I should see the expected money in section summary
        When I follow link back to report overview page
        Then I should see "money-in-short" as "no money in"

    @prof-pfa-low-not-started
    Scenario: A user has had a single item of money go in but nothing over £1k
        Given a Professional Admin has not started a Pfa Low Assets report
        When I view and start the money in short report section
        And I answer "Yes" to adding money in on the clients behalf
        And I am reporting on:
            | Benefit Type    |
            | Salary or wages |
        And I answer "no" to having one off payments over 1k
        Then I should see the expected money in section summary
        When I follow link back to report overview page
        Then I should see "money-in-short" as "money in"

    @prof-pfa-low-not-started
    Scenario: A user has had a multiple items of money go in but nothing over £1k
        Given a Professional Admin has not started a Pfa Low Assets report
        When I view and start the money in short report section
        And I answer "Yes" to adding money in on the clients behalf
        And I am reporting on:
            | Benefit Type                                        |
            | State pension and benefits                          |
            | Bequests - for example, inheritance, gifts received |
            | Income from investments, dividends, property rental |
            | Sale of investments, property or assets             |
            | Salary or wages                                     |
            | Compensations and damages awards                    |
            | Personal pension                                    |
        And I answer "no" to having one off payments over 1k
        Then I should see the expected money in section summary
        When I follow link back to report overview page
        Then I should see "money-in-short" as "money in"

    @prof-pfa-low-not-started
    Scenario: A user has had a single item of money go in and payment over £1k
        Given a Professional Admin has not started a Pfa Low Assets report
        When I view and start the money in short report section
        And I answer "Yes" to adding money in on the clients behalf
        And I am reporting on:
            | Benefit Type    |
            | Salary or wages |
        And I add 1 one-off payments over £1k
        Then I should see the expected money in section summary
        When I follow link back to report overview page
        Then I should see "money-in-short" as "1 item over £1,000"

    @prof-pfa-low-completed
    Scenario: A user edits money in section and adds a one off payment
        Given a Professional Admin has completed a Pfa Low Assets report
        When I edit the money in short section and add a payment
        Then I should see the expected money in section summary

    @prof-pfa-low-completed
    Scenario: A user tries to add a one off payment of less than £1k
        Given a Professional Admin has completed a Pfa Low Assets report
        When I add a one off money in payment that is less than £1k
        Then I should the see correct validation message

    @prof-pfa-low-completed
    Scenario: A user adds a single item of money in but nothing over £1k and submits report successfully
        Given a Professional Admin has completed a Pfa Low Assets report
        And I visit the report overview page
        Then I should see "money-in-short" as "no money in"
        When I visit the short money in summary section
        And I edit the money in short "exist" summary section
        And I answer "Yes" to adding money in on the clients behalf
        And I am reporting on:
            | Benefit Type    |
            | Salary or wages |
        And I answer "no" to one off payments over £1k
        Then I should see the expected money in section summary
        When I follow link back to report overview page
        Then I should see "money-in-short" as "money in"
        Given I submit the report
        Then my report should be submitted

    @prof-pfa-low-not-started
    Scenario: Transaction items over £1k are restored when user accidentally changes answer to having no one off payments
        Given a Professional Admin has not started a Pfa Low Assets report
        When I view and start the money in short report section
        And I answer "Yes" to adding money in on the clients behalf
        And I am reporting on:
            | Benefit Type    |
        And I add 3 one-off payments over £1k
        Then I should see the expected money in section summary
        And I edit the money in short "oneOffPaymentsExist" summary section
        And I answer "no" to having one off payments over 1k
        Then there should be "no" one off payments displayed on the money in summary page
        And I edit the money in short "oneOffPaymentsExist" summary section
        And I answer "yes" to having one off payments over 1k
        Then there should be "3" one off payments displayed on the money in summary page
