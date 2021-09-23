@client_benefits_check @v2 @v2_reporting_1 @acs
Feature: Client benefits check - NDR

#    @ndr-not-started
#    Scenario: A deputy has checked the clients benefit entitlement on a specific date
#        Given a Lay Deputy has not started an NDR report
#        And the deputies report ends and is due 'more' than 60 days after the client benefits check feature flag date
#        When I navigate to and start the client benefits check report section
#        And I confirm I checked the clients benefit entitlement on '01/01/2021'
##        And I confirm others receive income on the clients behalf
##        And I add 2 types of income with values
##        And I add a type of income where I don't know the value
#        Then the client benefits check summary page should contain the details I entered
#
#    @ndr-not-started
#    Scenario: A deputy is currently checking the clients benefit entitlement
#        Given a Lay Deputy has not started an NDR report
#        And the deputies report ends and is due 'more' than 60 days after the client benefits check feature flag date
#        When I navigate to and start the client benefits check report section
#        And I confirm I am currently checking the benefits the client is entitled to
##        And I confirm others do not receive income on the clients behalf
#        Then the client benefits check summary page should contain the details I entered
#
#    @ndr-not-started
#    Scenario: A deputy has never checked the clients benefits entitlement
#        Given a Lay Deputy has not started an NDR report
#        And the deputies report ends and is due 'more' than 60 days after the client benefits check feature flag date
#        When I navigate to and start the client benefits check report section
#        And I confirm I have never checked the benefits the client is entitled to and provide a reason
##        And I confirm others do not receive income on the clients behalf
#        Then the client benefits check summary page should contain the details I entered
#
#    @ndr-completed
#    Scenario: Reports due before the new question feature flag do not see the new report section and can submit report
#        Given a Lay Deputy has a completed NDR report
#        But they have not completed the client benefits section
#        And the deputies report ends and is due 'less' than 60 days after the client benefits check feature flag date
#        When I visit the report overview page
#        Then I should not see 'client-benefits-check' report section
#        And I should be able to submit my report without completing the section
#
#    @ndr-completed
#    Scenario: Reports due at least 60 days after the new question feature flag see the new report section
#        Given a Lay Deputy has a completed NDR report
#        But they have not completed the client benefits section
#        And the deputies report ends and is due 'more' than 60 days after the client benefits check feature flag date
#        When I visit the report overview page
#        Then I should see "client-benefits-check" as "not started"

#    Scenario: A deputy adds income other people receive on the client's behalf from summary page
#        Given a Lay Deputy has completed a Combined High Assets report
#        When I navigate to the client benefits check report section
#        And I add an income type from the summary page
#        Then the client benefits check summary page should contain the details I entered
#
#    Scenario: A deputy edits details of an income other people receive on the client's behalf
#        Given a Lay Deputy has not started a Combined High Assets report
#        When I navigate to and start the client benefits check report section
#        And I confirm I checked the clients benefit entitlement on '01/2021'
#        And I confirm others receive income on the clients behalf
#        And I add 1 type of income with values
#        And I 'edit' the last type of income I added
#        Then the client benefits check summary page should contain the details I entered
#
#    Scenario: A deputy removes details of an income other people receive on the client's behalf
#        Given a Lay Deputy has not started a Combined High Assets report
#        When I navigate to and start the client benefits check report section
#        And I confirm I checked the clients benefit entitlement on '01/2021'
#        And I confirm others receive income on the clients behalf
#        And I add 2 types of income with values
#        And I 'remove' the last type of income I added
#        Then the client benefits check summary page should contain the details I entered
