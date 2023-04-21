@v2 @v2_sequential @tools @report-reassignment
Feature: Report Reassignment - A super admin can re-assign reports between two different clients

    #Not tested, just outlining test cases and steps
    @super-admin
    Scenario: A super admin re-assigns previous reports from a previous client to a new client
        Given a Lay Deputy has not started a report
        When I view the report overview page
        Then I should see 1 report
        Given a super admin user accesses the admin app
        When I visit the Tools page
        And I select the Report Reassignment tool
        And I reassign the previous reports from a client to the new client
        Then I should see a confirmation message
        Given the Lay Deputy logs in again
        When I view the report overview page
        Then I should see 2 reports
