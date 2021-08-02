@v2 @report-management
Feature: Report Management (applies to all admin roles)

    @super-admin @prof-admin-completed
    Scenario: An admin user changes report type and due date for a in progress report
        Given a super admin user accesses the admin app
        And a Professional Deputy has completed a Pfa Low Assets report
        When I visit the admin client details page associated with the deputy I'm interacting with
        And I manage the deputies 'completed' report
        And I change the report type to 'Health and welfare'
        And I change the report due date to '3' weeks from now
        And I submit the new report details
        Then the report details should be updated

    @admin-manager @lay-combined-high-submitted @acs
    Scenario: An admin user un-submits a submitted report
        Given an admin manager user accesses the admin app
        And a Lay Deputy has submitted a Combined High Assets report
        When I visit the admin client details page associated with the deputy I'm interacting with
        And I manage the deputies 'submitted' report
        And I change the report 'start' date to '29 June 2021'
        And I change the report 'end' date to '28 June 2022'
        And I change the report due date to '4' weeks from now
        And I confirm all report sections are incomplete
        And I submit the new report details
        Then the report details should be updated
        When the user I'm interacting with logs in to the frontend of the app
        Then I should see the report sections the admin ticked as incomplete labelled as changes needed
#        And I should not be able to re-submit until I have confirmed I have completed the sections labelled as changes needed

    @admin
    Scenario: An admin user changes report type and due date for an un-submitted report
        Given

    @admin
    Scenario: An admin user closes an un-submitted report
        Given

    @admin
    Scenario: An admin user downloads a submitted report
        Given
