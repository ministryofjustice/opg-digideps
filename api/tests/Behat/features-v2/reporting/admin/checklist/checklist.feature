@v2 @reporting-checklist
Feature: Reporting Checklists - Reporting checklist

    @admin @lay-health-welfare-submitted
    Scenario: An admin submits the checklist form - applies to all admin roles
        Given an admin user accesses the admin app
        When I navigate to the clients search page
        And I search for the client
        And I click the clients details page link
        When I navigate to the clients report checklist page
        And I submit the checklist with the form filled in
        Then I should be redirected to the checklist submitted page

    @admin @lay-health-welfare-submitted
    Scenario: An admin submits the checklist form with errors - applies to all admin roles
        Given an admin user accesses the admin app
        When I navigate to the clients search page
        And I search for the client
        And I click the clients details page link
        When I navigate to the clients report checklist page
        And I submit the checklist without filling it in
        Then I should see all the validation errors
