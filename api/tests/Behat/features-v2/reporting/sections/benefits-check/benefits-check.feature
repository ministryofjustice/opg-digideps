@benefits_check @v2 @v2_reporting_1
Feature: Benefits check

    Scenario: A deputy has checked the clients benefit entitlement on a specific date
        Given a Lay Deputy has not started a Combined High Assets report
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '1 January 2021'
        And I confirm others receive income on the clients behalf
        And I add 2 types of income
        Then the client benefits check summary page should contain the details I entered

    Scenario: A deputy is currently checking the clients benefit entitlement
        Given a Lay Deputy has not started a Combined High Assets report
        When I navigate to and start the client benefits check report section
        And I confirm I am currently checking the benefits the client is entitled to
        And I confirm others do not receive income on the clients behalf
        Then the client benefits check summary page should contain the details I entered

    Scenario: A deputy has never checked the clients benefits entitlement
        Given a Lay Deputy has not started a Combined High Assets report
        When I navigate to and start the client benefits check report section
        And I confirm I have never checked the benefits the client is entitled to
        And I confirm others do not receive income on the clients behalf
        Then the client benefits check summary page should contain the details I entered

    Scenario: A deputy adds income other people receive on the client's behalf from summary page
        Given a Lay Deputy has completed a Combined High Assets report
        When I navigate to the client benefits check report section
        And I add an income type from the summary page
        Then the client benefits check summary page should contain the details I entered

    Scenario: A deputy edits details of an income other people receive on the client's behalf
        Given a Lay Deputy has not started a Combined High Assets report
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '1 January 2021'
        And I confirm others receive income on the clients behalf
        And I add 1 type of income
        And I 'edit' the last type of income I added
        Then the client benefits check summary page should contain the details I entered

    Scenario: A deputy removes details of an income other people receive on the client's behalf
        Given a Lay Deputy has not started a Combined High Assets report
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '1 January 2021'
        And I confirm others receive income on the clients behalf
        And I add 2 types of income
        And I 'remove' the last type of income I added
        Then the client benefits check summary page should contain the details I entered
