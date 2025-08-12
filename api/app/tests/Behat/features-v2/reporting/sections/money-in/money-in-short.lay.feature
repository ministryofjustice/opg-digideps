@v2 @v2_reporting_2 @money-in-low-assets
Feature: Money in Low Assets - Lay users

    @lay-pfa-low-not-started
    Scenario: A user has had no money go in
        Given a Lay Deputy has not started a Pfa Low Assets report
        And I visit the report overview page
        Then I should see "money-in-short" as "not started"
        When I view and start the money in short report section
        And I confirm "No" to adding money in on the clients behalf
        And I enter a reason for no money in short
        Then I should see the expected money in section summary
        When I follow link back to report overview page
        Then I should see "money-in-short" as "no money in"

    @lay-pfa-low-not-started
    Scenario: A user has had a single item of money go in but nothing over £1k
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the money in short report section
        And I answer "Yes" to adding money in on the clients behalf
        And I am reporting on:
            | Benefit Type    |
            | Salary or wages |
        And I answer "no" to one off payments over £1k
        Then I should see the expected money in section summary
        When I follow link back to report overview page
        Then I should see "money-in-short" as "money in"

    @lay-pfa-low-not-started
    Scenario: A user has had a multiple items of money go in but nothing over £1k
        Given a Lay Deputy has not started a Pfa Low Assets report
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
        And I answer "no" to one off payments over £1k
        Then I should see the expected money in section summary
        When I follow link back to report overview page
        Then I should see "money-in-short" as "money in"

    @lay-pfa-low-not-started
    Scenario: A user has had a single item of money go in and payment over £1k
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the money in short report section
        And I answer "Yes" to adding money in on the clients behalf
        And I am reporting on:
            | Benefit Type    |
            | Salary or wages |
        And I add 1 one-off payments over £1k
        Then I should see the expected money in section summary
        When I follow link back to report overview page
        Then I should see "money-in-short" as "1 item over £1,000"

    @lay-pfa-low-completed
    Scenario: A user edits money in section and adds a one off payment
        Given a Lay Deputy has completed a Pfa Low Assets report
        When I edit the money in short section and add a payment
        Then I should see the expected money in section summary

    @lay-pfa-low-completed
    Scenario: A user tries to add a one off payment of less than £1k
        Given a Lay Deputy has completed a Pfa Low Assets report
        When I add a one off money in payment that is less than £1k
        Then I should the see correct validation message

    @lay-pfa-low-completed
    Scenario: A user adds a single item of money in but nothing over £1k and submits report successfully
        Given a Lay Deputy has completed a Pfa Low Assets report
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
        Given I follow the submission process to the declaration page
        Then I can go back to the contact details page
        And I can go back to the report review page
        And I can go back to the report overview page
        Then I follow the submission process to the declaration page
        And I fill in the declaration page and submit the report
        Then my report should be submitted

    @lay-pfa-low-not-started
    Scenario: Transaction items over £1k are restored when user accidentally changes answer to reporting no money in
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the money in short report section
        And I answer "Yes" to adding money in on the clients behalf
        And I am reporting on:
            | Benefit Type    |
        And I add 3 one-off payments over £1k
        Then I should see the expected money in section summary
        When I edit the money in short "exist" summary section
        And I answer "No" to adding money in on the clients behalf
        And I enter a reason for no money in short
        Then there should be "no" one off payments displayed on the money in summary page
        When I edit the money in short "exist" summary section
        Then I answer "Yes" to adding money in on the clients behalf
        And I am reporting on:
            | Benefit Type    |
        And I answer "yes" to one off payments over £1k
        Then there should be "3" one off payments displayed on the money in summary page

    @lay-pfa-low-not-started
    Scenario: A user adds a transaction item and then removes it and reports to having no money in then adds a new transaction item
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the money in short report section
        And I answer "Yes" to adding money in on the clients behalf
        And I am reporting on:
            | Benefit Type    |
        And I add 1 one-off payments over £1k
        Then I should see the expected money in section summary
        When I delete the transaction from the summary page
        Then there should be "no" one off payments displayed on the money in summary page
        Then I edit the answer to the money in one off payment over 1k
        And I add 1 one-off payments over £1k
        Then there should be "1" one off payments displayed on the money in summary page
