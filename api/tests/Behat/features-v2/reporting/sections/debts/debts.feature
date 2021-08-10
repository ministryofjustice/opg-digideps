@v2 @debts @acs
Feature: Report debts

    @lay-pfa-low-not-started
    Scenario: A user has no debts
        Given a Lay Deputy has not started a Pfa Low Assets report
        And I visit the report overview page
        Then I should see "debts" as "not started"
        When I view and start the debts report section
        And I have no debts
        Then I should see the expected debts section summary
        When I follow link back to report overview page
        Then I should see "debts" as "finished"

    @lay-pfa-low-not-started
    Scenario: A user has some debts
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the debts report section
        And I have a debt to add
        And I add some debt values
        And I say how the debts are being managed
        Then I should see the expected debts section summary
        When I follow link back to report overview page
        Then I should see "debts" as "finished"

    @lay-pfa-low-completed
    Scenario: A user edits a debt
        Given a Lay Deputy has completed a Pfa Low Assets report
        When I edit an existing debt payment
        Then I should see the expected debts section summary

    @lay-pfa-low-not-started
    Scenario: A user tries to add a debt with invalid amount
        Given a Lay Deputy has not started a Pfa Low Assets report
        When I view and start the debts report section
        When I add a debt with invalid amount
        Then I should see the validation message
