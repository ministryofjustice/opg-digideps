@v2 @court-order
Feature: Court order page

    @lay-combined-high-submitted @court-order-needs-login
    Scenario: A logged out Deputy cannot view the page
        Given I visit the court order page
        Then they get redirected back to the log in page

    @lay-health-welfare-not-started @court-order-view-one
    Scenario: A logged in deputy views their court order
        Given a Lay Deputy has not started a Health and Welfare report
        And I am associated with '1' 'hw' court order(s)
        When I visit the page of a court order that 'I am' associated with
        Then I should be on the court order page

    @lay-pfa-low-not-started @court-order-no-access
    Scenario: A logged in deputy cannot view a court order that's not assigned to them
        Given a Lay Deputy has not started a Pfa Low Assets report
        And I am associated with '1' 'pfa' court order(s)
        When I visit the page of a court order that 'I am not' associated with
        Then I should be redirected and denied access to view the court order

    @lay-pfa-low-completed @court-order-discharged
    Scenario: A deputy can no longer view their court order once they've been discharged from the court order
        Given a Lay Deputy has completed a Pfa Low Assets report
        And I am associated with '1' 'pfa' court order(s)
        When I visit the page of a court order that 'I am' associated with
        Then I should be on the court order page
        When I am discharged from the court order
        And I visit the page of a court order that 'I am' associated with
        Then I should be redirected and denied access to view the court order

    @lay-pfa-high-not-started-multi-client-deputy @court-order-view-all
    Scenario: A multi client deputy can view all of their court orders
        When a lay deputy with no court orders logs in
        And I am associated with '3' 'pfa' court order(s)
        When I visit the multiple court order page
        Then I should see '3' court orders on the page
        When I visit the court order page of the 'first' court order that 'I am' associated with
        Then I should be on the court order page
        When I visit the court order page of the 'second' court order that 'I am' associated with
        Then I should be on the court order page
        And the report status should be 'not started'

    @lay-pfa-high-not-started-multi-client-deputy @court-order-no-message
    Scenario: A lay deputy logs in but has no court orders associated with them yet
        When a lay deputy with no court orders logs in
        And I visit the multiple court order page
        Then I should see a message explaining that my account is being set up

    @lay-pfa-low-not-started @lay-pfa-court-order-co-deputy @co-deputy-unregistered
    Scenario: Court order with invited co-deputy who is awaiting registration
        Given a Lay Deputy has not started a Pfa Low Assets report
        And I am associated with a 'pfa' court order
        And an unregistered co-deputy is associated with the court order
        When I visit the page of a court order that 'I am' associated with
        Then I should see that I am a registered deputy
        And I should see that the co-deputy is awaiting registration

    @lay-pfa-low-not-started @lay-pfa-court-order-co-deputy @court-order-co-deputy-registered
    Scenario: Court order with invited co-deputy who has registered
        Given a Lay Deputy has not started a Pfa Low Assets report
        And I am associated with a 'pfa' court order
        And a registered co-deputy is associated with the court order
        When I visit the page of a court order that 'I am' associated with
        Then I should see that I am a registered deputy
        And I should see that the co-deputy is registered

    @lay-pfa-high-submitted @court-order-report-type-changed
    Scenario: A court order with two different report types is shown as a single court order with the latest report's type (DDLS-1003)
        Given a Lay Deputy has submitted a Pfa High Assets report
        And I am associated with a 'pfa' court order
        And the latest unsubmitted report for the court order is a Pfa High Assets report
        When I visit the multiple court order page
        # should be redirected to the court order, which is treated as a single court order despite having two different report types
        Then I should be on the court order page
