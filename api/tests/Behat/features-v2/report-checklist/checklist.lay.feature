@v2 @v2_admin @reporting-checklist-lay
Feature: Reporting Checklists - Lay reporting checklist

    @admin @lay-combined-high-submitted @acs
    Scenario: An admin submits the checklist form - applies to all admin roles
        Given a Lay Deputy has submitted a Combined High Assets report
        And the deputies 'previous' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        And an admin user accesses the admin app
        When I navigate to the clients search page
        And I search for the client I'm interacting with
        And I click the clients details page link
        And I navigate to the clients report checklist page
        And I submit the checklist with the form filled in
        Then I should be redirected to the checklist submitted page

    @admin @lay-health-welfare-submitted
    Scenario: An admin submits the checklist form with errors - applies to all admin roles
        Given a Lay Deputy has submitted a health and welfare report
        And an admin user accesses the admin app
        When I navigate to the clients search page
        And I search for the client I'm interacting with
        And I click the clients details page link
        And I navigate to the clients report checklist page
        And I submit the checklist without filling it in
        Then I should see all the validation errors

    @admin @lay-health-welfare-submitted
    Scenario: An lay hw checklist does not contain the public authority hw specific sections - applies to all admin roles
        Given a Lay Deputy has submitted a health and welfare report
        And an admin user accesses the admin app
        When I navigate to the clients search page
        And I search for the client I'm interacting with
        And I click the clients details page link
        And I navigate to the clients report checklist page
        Then I can only see the 'lay hw' specific section
