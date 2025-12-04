@v2 @v2_admin @report-management
Feature: Report Management (applies to all admin roles)

    @super-admin @prof-admin-health-welfare-completed
    Scenario: An admin user changes report type and due date for a in progress report
        Given a Professional Deputy has completed a Pfa Low Assets report
        And a super admin user accesses the admin app
        When I visit the admin client details page associated with the deputy I'm interacting with
        And I manage the deputies 'completed' report
        And I change the report type to 'Health and welfare'
        And I change the report due date to '3' weeks from now
        And I submit the new report details
        Then the report details should be updated

    @admin-manager @lay-combined-high-submitted
    Scenario: An admin user un-submits a submitted report
        Given a Lay Deputy has submitted a Combined High Assets report
        And an admin manager user accesses the admin app
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

    @admin-manager @lay-combined-high-submitted
    Scenario: An admin user un-submits a submitted report and changes the due date at the same time
        Given a Lay Deputy has submitted a Combined High Assets report
        And an admin manager user accesses the admin app
        When I visit the admin client details page associated with the deputy I'm interacting with
        And I manage the deputies 'submitted' report
        And I set the due date of the report to a custom date
        And I confirm all report sections are incomplete
        And I submit the new report details
        Then the report details should be updated
        When the user I'm interacting with logs in to the frontend of the app
        Then I should see the report sections the admin ticked as incomplete labelled as changes needed

    @admin @pa-admin-combined-high-submitted
    Scenario: An admin user changes report type and due date for an un-submitted report
        Given a Public Authority Deputy has submitted a Combined High Assets report
        And an admin user accesses the admin app
        When I visit the admin client details page associated with the deputy I'm interacting with
        And I manage the deputies 'submitted' report
        And I confirm all report sections are incomplete
        And I submit the new report details
        And I manage the deputies 'un-submitted' report
        And I change the report due date to '5' weeks from now
        And I change the report type to 'Health and welfare'
        And I submit the new report details
        Then the report details should be updated

    @admin @pa-admin-combined-high-submitted
    Scenario: An admin user closes an un-submitted report
        Given a Public Authority Deputy has submitted a Combined High Assets report
        And an admin user accesses the admin app
        When I visit the admin client details page associated with the deputy I'm interacting with
        And I manage the deputies 'submitted' report
        And I confirm all report sections are incomplete
        And I submit the new report details
        And I close the un-submitted report
        Then the report should should show as submitted

    @super-admin @pa-admin-combined-high-submitted
    Scenario: A super admin can download a submitted report PDF
        Given a Public Authority Deputy has submitted a Combined High Assets report
        And a super admin user accesses the admin app
        When I visit the admin client details page associated with the deputy I'm interacting with
        Then the link to download the submitted report should be visible

    @admin @admin-manager @pa-admin-combined-high-submitted
    Scenario: An admin manager cannot download a submitted report PDF
        Given a Public Authority Deputy has submitted a Combined High Assets report
        Given an admin manager user accesses the admin app
        When I visit the admin client details page associated with the deputy I'm interacting with
        Then the link to download the submitted report should not be visible

    @admin @admin-manager @pa-admin-combined-high-submitted
    Scenario:An admin cannot download a submitted report PDF
        Given a Public Authority Deputy has submitted a Combined High Assets report
        Given an admin manager user accesses the admin app
        When I visit the admin client details page associated with the deputy I'm interacting with
        Then the link to download the submitted report should not be visible

    @admin-manager @lay-combined-high-submitted
    Scenario: An admin manager un-submits a report that did not have a completed client benefits check section
        Given a Lay Deputy has submitted a Combined High Assets report
        But they have not completed the client benefits section for their 'previous' report
        And the deputies 'previous' report ends and is due 'less' than 60 days after the client benefits check feature flag date
        And an admin manager user accesses the admin app
        When I visit the admin client details page associated with the deputy I'm interacting with
        And I manage the deputies 'submitted' report
        And I should not see the client benefits check section in the checklist group
        And I confirm all report sections are incomplete
        And I submit the new report details
        Then the report details should be updated
        When the user I'm interacting with logs in to the frontend of the app
        Then I should see the report sections the admin ticked as incomplete labelled as changes needed
        And I should be able to submit my 'previous' report without completing the client benefits check section

    @admin-manager @lay-combined-high-submitted
    Scenario: An admin manager un-submits a report that had a completed client benefits check section
        Given a Lay Deputy has submitted a Combined High Assets report
        And the deputies 'previous' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        And an admin manager user accesses the admin app
        When I visit the admin client details page associated with the deputy I'm interacting with
        And I manage the deputies 'submitted' report
        And I change the report 'start' date to '29 June 2015'
        And I change the report 'end' date to '28 June 2016'
        And I should see the client benefits check section in the checklist group
        And I confirm all report sections are incomplete
        And I submit the new report details
        Then the report details should be updated
        When the user I'm interacting with logs in to the frontend of the app
        Then I should see the report sections the admin ticked as incomplete labelled as changes needed
        Then I follow the submission process to the declaration page for previous report
        And I fill in the declaration page and submit the report
        Then my report should be submitted
