@v2 @v2_admin @tools @report-reassignment
Feature: Report Reassignment - A super admin can re-assign reports between two different clients

    @super-admin @lay-pfa-high-not-started @lay-pfa-high-submitted
    Scenario: A super admin re-assigns previous reports from a previous client to a new client
        Given a Lay Deputy has not started a report
        Then I should see 1 report
        Given a super admin user accesses the admin app
        When I visit the admin tools page
        And I select the Report Reassignment tool
        And I reassign the previous reports from a client to the new client
        Given a Lay Deputy logs in again
        Then I should see 2 reports
