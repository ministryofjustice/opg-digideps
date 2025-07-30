@v2 @court-order-invite
Feature: Court order - sending invitations to other deputies

    @lay-pfa-low-not-started
    Scenario: Deputy can invite a co-deputy present in the pre-reg table to a court order
        When a Lay Deputy has not started a Pfa Low Assets report
        And I am associated with '1' 'pfa' court order(s)
        Given I invite a co-deputy to the court order
        When I visit the page of a court order that 'I am' associated with
        Then I should see that I am a registered deputy
        And I should see that the invited co-deputy is awaiting registration
