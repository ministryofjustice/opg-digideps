@v2 @reporting-checklist-org
Feature: Reporting Checklists - Org reporting checklist

    @admin @prof-named-pfa-high-submitted
    Scenario: An professional deputy with high assets checklist does not contain lay hw specific sections - applies to all admin roles
        Given an admin user accesses the admin app
        When I navigate to the clients search page
        And I search for the 'prof' client
        And I click the clients details page link
        When I navigate to the clients report checklist page
        Then I can only see the 'prof pfa high' specific section

    @admin @pa-named-pfa-high-submitted
    Scenario: An lay hw checklist does not contain public authority with high assets specific sections - applies to all admin roles
        Given an admin user accesses the admin app
        When I navigate to the clients search page
        And I search for the 'pa' client
        And I click the clients details page link
        When I navigate to the clients report checklist page
        Then I can only see the 'public authority pfa high' specific section
