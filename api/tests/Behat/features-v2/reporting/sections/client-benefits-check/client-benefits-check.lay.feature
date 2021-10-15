@client_benefits_check @v2 @v2_reporting_1 @acs
Feature: Client benefits check - Lay users

    @lay-combined-high-not-started
    Scenario: A deputy has checked the clients benefit entitlement on a specific date
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I visit the report overview page
        Then I should see "client-benefits-check" as "not started"
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '01/01/2021'
        And I confirm others receive income on the clients behalf
        And I add 2 types of income with values
        And I add a type of income where I don't know the value
        And I have no further types of income to add
        Then the client benefits check summary page should contain the details I entered

    @lay-combined-high-not-started
    Scenario: A deputy is currently checking the clients benefit entitlement
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I navigate to and start the client benefits check report section
        And I confirm I am currently checking the benefits the client is entitled to
        And I confirm others do not receive income on the clients behalf
        Then the client benefits check summary page should contain the details I entered
        And I should not see an empty section for income types

    @lay-combined-high-not-started
    Scenario: A deputy has never checked the clients benefits entitlement
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I navigate to and start the client benefits check report section
        And I confirm I have never checked the benefits the client is entitled to and provide a reason
        And I confirm others do not receive income on the clients behalf
        Then the client benefits check summary page should contain the details I entered
        And I should not see an empty section for income types

    @lay-combined-high-completed
    Scenario: Reports due before the new question feature flag do not see the new report section and can submit report
        Given a Lay Deputy has completed a Combined High Assets report
        But they have not completed the client benefits section
        And the deputies report ends and is due 'less' than 60 days after the client benefits check feature flag date
        When I visit the report overview page
        Then I should not see 'client-benefits-check' report section
        And I should be able to submit my report without completing the section

    @lay-combined-high-completed
    Scenario: Reports due at least 60 days after the new question feature flag see the new report section
        Given a Lay Deputy has completed a Combined High Assets report
        But they have not completed the client benefits section
        And the deputies report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I visit the report overview page
        Then I should see "client-benefits-check" as "finished"

    @lay-combined-high-completed
    Scenario: A deputy adds income other people receive on the client's behalf from summary page
        Given a Lay Deputy has completed a Combined High Assets report
        And the deputies report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I visit the client benefits check summary page
        And I add 3 income types from the summary page
        Then the client benefits check summary page should contain the details I entered

    @lay-combined-high-not-started
    Scenario: A deputy edits details of an income other people receive on the client's behalf
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '01/01/2021'
        And I confirm others receive income on the clients behalf
        And I add 1 type of income with values
        And I have no further types of income to add
        And I 'edit' the last type of income I added
        Then the client benefits check summary page should contain the details I entered

    @lay-combined-high-not-started
    Scenario: A deputy removes details of an income other people receive on the client's behalf
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '01/01/2021'
        And I confirm others receive income on the clients behalf
        And I add 2 types of income with values
        And I have no further types of income to add
        And I 'remove' the last type of income I added
        Then the client benefits check summary page should contain the details I entered

#    (Testing forms have some validation. Full validation tests are in the individual validator or entity unit tests.)
    @lay-combined-high-not-started
    Scenario: A deputy attempts to submit invalid data during the form steps
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement but dont provide a date
        Then I should see a 'missing date' error
        Given I confirm I checked the clients benefit entitlement on '01/01/2021'
        And I confirm I dont know if anyone else receives income on the clients behalf and dont provide an explanation
        Then I should see a 'missing explanation' error
        Given I confirm others receive income on the clients behalf
        And I confirm the amount but don't provide an income type
        Then I should see a 'missing income type' error
        When I visit the report overview page
        Then I should see "client-benefits-check" as "not finished"
