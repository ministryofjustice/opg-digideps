@client_benefits_check @v2 @v2_reporting_1
Feature: Client benefits check - NDR users

    @ndr-not-started
    Scenario: A deputy has checked the clients benefit entitlement on a specific date
        Given a Lay Deputy has not started an NDR report
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '01/01/2021'
        And I confirm others receive money on the clients behalf
        And I add 2 types of money with values
        And I add a type of money where I don't know the value
        And I have no further types of money to add
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
        And I follow the submission process to the declaration page for current report
        And I fill in the declaration page and submit the report
        Then my report should be submitted

    @ndr-not-started
    Scenario: A deputy removes an item of money they've added
        Given a Lay Deputy has not started an NDR report
        When I navigate to and start the client benefits check report section
        And I confirm I checked the clients benefit entitlement on '01/01/2021'
        And I confirm others receive money on the clients behalf
        And I add 2 types of money with values
        And I add a type of money where I don't know the value
        And I have no further types of money to add
        And I 'remove' the last type of money I added
        Then the client benefits check summary page should contain the details I entered

    @ndr-not-started
    Scenario: A deputy edits details of a completed form
        Given a Lay Deputy has not started an NDR report
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
