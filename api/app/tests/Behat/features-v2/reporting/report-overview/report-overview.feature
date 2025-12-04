@v2 @v2_reporting_1 @report-overview
Feature: Report Overview - All User Roles

    @prof-admin-health-welfare-not-started
    Scenario: A Professional User can see client and deputy details
        Given a Professional Admin Deputy has not started a report
        When I view the report overview page
        Then I should see the correct client details
        And I should see the correct deputy details

    @pa-admin-health-welfare-not-started
    Scenario: A Public Authority User can see client and deputy details
        Given a Public Authority Admin Deputy has not started a report
        When I view the report overview page
        Then I should see the correct client details
        And I should see the correct deputy details

    @lay-pfa-high-not-started-multi-client-deputy-with-ndr
    Scenario: A user logs in with their ndr enabled primary account and can access both clients and associated reports
        When a Lay Deputy tries to login with their "primary" email address
        And they choose their "primary" Client
        Then they should be on the "primary" Client's dashboard
        And I 'should' see the NDR report on the reports page
        Then the Lay deputy navigates to the Choose a client page
        And they choose their "non-primary" Client
        Then they should be on the "non-primary" Client's dashboard
        And I 'should not' see the NDR report on the reports page
