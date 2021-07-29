@v2 @report-management
Feature: Report Management (applies to all admin roles)

    @super-admin @prof-admin-completed @acs
    Scenario: An admin user changes report type and due date for a in progress report
        Given a super admin user accesses the admin app
        And a Professional Deputy has completed a Pfa Low Assets report
        When I visit the admin client details page associated with the deputy I'm interacting with
        And I manage the deputies 'completed' report
        And I change the report type to 'Health and Welfare'
        And I change the report due date to '3' weeks from now
        And I submit the new report details
        Then the report details should be updated

    @admin-manager @prof-admin-submitted
    Scenario: An admin user un-submits a submitted report
        Given a Professional Deputy has submitted a Health and Welfare report
        When I visit the client summary page associated with the deputy
        And I manage the submitted report

    @admin
    Scenario: An admin user changes report type and due date for an un-submitted report
        Given

    @admin
    Scenario: An admin user closes an un-submitted report
        Given

    @admin
    Scenario: An admin user downloads a submitted report
        Given
