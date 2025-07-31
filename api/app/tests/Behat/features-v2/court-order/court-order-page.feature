@v2 @court-order
Feature: Court order page

    @lay-combined-high-submitted
    Scenario: A logged out Deputy cannot view the page
        Given I visit the court order page
        Then they get redirected back to the log in page

    @lay-health-welfare-not-started
    Scenario: A logged in deputy views their court order
        Given a Lay Deputy has not started a Health and Welfare report
        And I am associated with '1' 'hw' court order(s)
        When I visit the page of a court order that 'I am' associated with
        Then I should be on the court order page

    @lay-pfa-low-not-started
    Scenario: A logged in deputy cannot view a court order that's not assigned to them
        Given a Lay Deputy has not started a Pfa Low Assets report
        And I am associated with '1' 'pfa' court order(s)
        When I visit the page of a court order that 'I am not' associated with
        Then I should be redirected and denied access to view the court order

    @lay-pfa-low-completed
    Scenario: A deputy can no longer view their court order once they've been discharged from the court order
        Given a Lay Deputy has completed a Pfa Low Assets report
        And I am associated with '1' 'pfa' court order(s)
        When I visit the page of a court order that 'I am' associated with
        Then I should be on the court order page
        When I am discharged from the court order
        Then I should be redirected and denied access to view the court order

    @lay-pfa-high-not-started-multi-client-deputy
    Scenario: A multi client deputy can view all of their court orders
        When a Lay Deputy tries to login with their "primary" email address
        And I am associated with '3' 'pfa' court order(s)
        When I visit the multiple court order page
        Then I should see '3' court orders on the page
        When I visit the court order page of the 'first' court order that 'I am' associated with
        Then I should be on the court order page
        When I visit the court order page of the 'second' court order that 'I am' associated with
        Then I should be on the court order page

    @lay-pfa-no-court-orders-message @lay-pfa-high-not-started-multi-client-deputy
    Scenario: A lay deputy logs in but has no court orders associated with them yet
        When a lay deputy with no court orders logs in
        And I visit the multiple court order page
        Then I should see a message explaining that my account is being set up

    @lay-pfa-with-ndr-not-started
    Scenario: A deputy can view their NDR on their PFA court order
        Given a Lay Deputy has not started an NDR report
        And I am associated with '1' 'pfa' court order(s)
        When I visit the page of a court order that 'I am' associated with
        Then I should be on the court order page
        And I should see an NDR on the court order page with a status of 'Not started' with standard report status of 'Not available'
        Then I can procced to fill out the NDR

    @lay-pfa-low-not-started @lay-pfa-court-order-co-deputy @lay-pfa-court-order-co-deputy-unregistered
    Scenario: Court order with invited co-deputy who is awaiting registration
        Given a Lay Deputy has not started a Pfa Low Assets report
        And I am associated with a 'pfa' court order
        And an unregistered co-deputy is associated with the court order
        When I visit the page of a court order that 'I am' associated with
        Then I should see that I am a registered deputy
        And I should see that the co-deputy is awaiting registration

    @lay-pfa-low-not-started @lay-pfa-court-order-co-deputy @lay-pfa-court-order-co-deputy-registered
    Scenario: Court order with invited co-deputy who is awaiting registration
        Given a Lay Deputy has not started a Pfa Low Assets report
        And I am associated with a 'pfa' court order
        And a registered co-deputy is associated with the court order
        When I visit the page of a court order that 'I am' associated with
        Then I should see that I am a registered deputy
        And I should see that the co-deputy is registered
