@report-checklist @checklist-sync @v2_sequential @v2
Feature: Synchronising Checklists with Sirius

    @super-admin @lay-health-welfare-submitted
    Scenario: Completing a report checklist sets the status of the checklist to queued and running the sync command syncs the checklist
        Given a Lay Deputy has submitted a health and welfare report
        And a super admin user accesses the admin app
        When I visit the admin client details page associated with the deputy I'm interacting with
        And I navigate to the clients report checklist page
        And I submit the checklist with the form filled in
        When I visit the checklist page for the previously submitted report for the user I am interacting with
        Then the checklist status should be 'queued'
        When I run the checklist-sync command
        And I visit the checklist page for the previously submitted report for the user I am interacting with
        Then the checklist status should be 'synced'

    @super-admin @lay-health-welfare-submitted @acs
    Scenario: Reports associated with a discharged deputy (deleted client) successfully syncs with Sirius
        Given a Lay Deputy has submitted a health and welfare report
        And the deputy I am interacting with has been discharged
        And a super admin user accesses the admin app
        When I visit the admin client details page associated with the deputy I'm interacting with
        And I navigate to the clients report checklist page
        And I submit the checklist with the form filled in
        And I visit the checklist page for the previously submitted report for the user I am interacting with
        And I run the checklist-sync command
        And I visit the checklist page for the previously submitted report for the user I am interacting with
        Then the checklist status should be 'synced'
