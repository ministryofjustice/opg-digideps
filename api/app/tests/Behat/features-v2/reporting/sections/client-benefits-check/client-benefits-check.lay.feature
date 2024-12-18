@client_benefits_check @v2 @v2_reporting_1
Feature: Client benefits check - Lay users

    @lay-combined-high-not-started
    Scenario: A deputy has checked the clients benefit entitlement on a specific date
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies 'current' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I visit the report overview page
        Then I should see "client-benefits-check" as "not started"
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '01/01/2021'
        And I confirm others receive money on the clients behalf
        And I add 2 types of money with values
        And I add a type of money where I don't know the value
        And I have no further types of money to add
        Then the client benefits check summary page should contain the details I entered

    @lay-combined-high-not-started
    Scenario: A deputy is currently checking the clients benefit entitlement
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies 'current' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I navigate to and start the client benefits check report section
        And I confirm I am currently checking the benefits the client is entitled to
        And I confirm others do not receive money on the clients behalf
        Then the client benefits check summary page should contain the details I entered
        And I should not see an empty section for money types

    @lay-combined-high-not-started
    Scenario: A deputy has never checked the clients benefits entitlement
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies 'current' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I navigate to and start the client benefits check report section
        And I confirm I have never checked the benefits the client is entitled to and provide a reason
        And I confirm others do not receive money on the clients behalf
        Then the client benefits check summary page should contain the details I entered
        And I should not see an empty section for money types

    @lay-combined-high-not-started
    Scenario: A deputy does not know if others receive money on clients behalf
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies 'current' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I navigate to and start the client benefits check report section
        And I confirm I have never checked the benefits the client is entitled to and provide a reason
        And I confirm I do not know if others receive money on the clients behalf and provide an explanation
        Then the client benefits check summary page should contain the details I entered
        And I should not see an empty section for money types

    @lay-combined-high-completed
    Scenario: Reports due before the new question feature flag do not see the new report section and can submit report
        Given a Lay Deputy has completed a Combined High Assets report
        But they have not completed the client benefits section for their 'current' report
        And the deputies 'current' report ends and is due 'less' than 60 days after the client benefits check feature flag date
        When I visit the report overview page
        Then I should not see 'client-benefits-check' report section
        And I should be able to submit my 'current' report without completing the client benefits check section

    @lay-combined-high-completed
    Scenario: Reports due at least 60 days after the new question feature flag see the new report section
        Given a Lay Deputy has completed a Combined High Assets report
        And the deputies 'current' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I visit the report overview page
        Then I should see "client-benefits-check" as "finished"

    @lay-combined-high-completed
    Scenario: A deputy adds money other people receive on the client's behalf from summary page
        Given a Lay Deputy has completed a Combined High Assets report
        And the deputies 'current' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I visit the client benefits check summary page
        And I add 3 money types from the summary page
        Then the client benefits check summary page should contain the details I entered

    @lay-combined-high-not-started
    Scenario: A deputy edits details of a completed form
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies 'current' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I navigate to and start the client benefits check report section
        And I confirm I have never checked the benefits the client is entitled to and provide a reason
        And I confirm others receive money on the clients behalf
        And I add 1 type of money with values
        And I have no further types of money to add
        And I 'edit' the last type of money I added
        Then the client benefits check summary page should contain the details I entered
        Given I edit my response to do others receive money on a clients behalf to 'no'
        Then the client benefits check summary page should contain my updated response and no money types
        Given I edit my response to when I last checked the clients benefit entitlement to currently checking
        Then the client benefits check summary page should contain the details I entered

    @lay-combined-high-not-started
    Scenario: A deputy removes details of an money other people receive on the client's behalf
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies 'current' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '01/01/2021'
        And I confirm others receive money on the clients behalf
        And I add 2 types of money with values
        And I have no further types of money to add
        And I 'remove' the last type of money I added
        Then the client benefits check summary page should contain the details I entered

#    (Testing forms have some validation. Full validation tests are in the individual validator or entity unit tests.)
    @lay-combined-high-not-started
    Scenario: A deputy attempts to submit invalid data during the form steps
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies 'current' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement but dont provide a date
        Then I should see a 'missing date' error on client benefits check summary page
        Given I confirm I checked the clients benefit entitlement on '01/01/2021'
        And I confirm I dont know if anyone else receives money on the clients behalf and dont provide an explanation
        Then I should see a 'missing explanation' error on client benefits check summary page
        Given I confirm others receive money on the clients behalf
        And I confirm the amount but don't provide a money type
        Then I should see a 'missing money type' error on client benefits check summary page
        Given I fill in amount and description but dont provide details on who received the money
        Then I should see a 'missing who received money' error on client benefits check summary page
        When I visit the report overview page
        Then I should see "client-benefits-check" as "not finished"

    @lay-combined-high-not-started
    Scenario: A deputy confirms others receive money on client's behalf and then changes their mind
        Given a Lay Deputy has not started a Combined High Assets report
        And the deputies 'current' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '01/01/2021'
        And I confirm others receive money on the clients behalf
        And I attempt to submit an empty money type
        Then I should see a 'at least one money type required' error on client benefits check summary page
        And I change my mind and go back to the previous page
        And I confirm others do not receive money on the clients behalf
        Then the client benefits check summary page should contain the details I entered
