@client_benefits_check @v2 @v2_reporting_1
Feature: Client benefits check - NDR users

    @ndr-not-started
    Scenario: A deputy has checked the clients benefit entitlement on a specific date
        Given a Lay Deputy has not started an NDR report
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '01/01/2021'
        And I confirm others receive income on the clients behalf
        And I add 2 types of income with values
        And I add a type of income where I don't know the value
        And I have no further types of income to add
        Then the client benefits check summary page should contain the details I entered

    @ndr-not-started
    Scenario: A deputy has completed some of the client benefits section check
        Given a Lay Deputy has not started an NDR report
        When I visit the report overview page
        Then I should see "client-benefits-check" as "not started"
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '01/01/2021'
        When I visit the report overview page
        Then I should see "client-benefits-check" as "not finished"

    @ndr-completed
    Scenario: Completed reports can be submitted with new section
        Given a Lay Deputy has a completed NDR report
        When I visit the report overview page
        Then I should see "client-benefits-check" as "finished"
        And I should be able to submit the report
