@v2 @court-order-invite
Feature: Court order - sending invitations to other deputies

    @lay-pfa-low-not-started @court-order-invite-success
    Scenario: Deputy can invite a co-deputy present in the pre-reg table to a court order
        Given a Lay Deputy has not started a Pfa Low Assets report
        And I am associated with '1' 'pfa' court order(s)
        And I visit the page of a court order that 'I am' associated with
        And I click on "invite-codeputy-button"
        When I invite a co-deputy to the court order
        Then I should be on the page for the court order
        And I should see that I am a registered deputy
        And I should see that the invited co-deputy is awaiting registration

    @lay-pfa-low-not-started @court-order-invite-cancel
    Scenario: Court order invite page cancel button redirects to page for the court order
        Given a Lay Deputy has not started a Pfa Low Assets report
        And I am associated with '1' 'pfa' court order(s)
        When I visit the court order invite page
        And I click on "cancel-invitation"
        Then I should be on the page for the court order
