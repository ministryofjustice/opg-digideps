@v2 @v2_admin @reporting-checklist-org
Feature: Reporting Checklists - Org reporting checklist

    @admin @prof-deputy-pfa-high-submitted
    Scenario: An professional deputy with high assets checklist does not contain lay hw specific sections - applies to all admin roles
        Given a Professional Deputy has submitted a Pfa High Assets report
        And an admin user accesses the admin app
        When I navigate to the clients search page
        And I search for the client I'm interacting with
        And I click the clients details page link
        When I navigate to the clients report checklist page
        Then I can only see the 'prof pfa high' specific section

    @admin @pa-deputy-pfa-high-submitted
    Scenario: An lay hw checklist does not contain public authority with high assets specific sections - applies to all admin roles
        Given a Public Authority Named Deputy has submitted a Pfa High Assets report
        And an admin user accesses the admin app
        When I navigate to the clients search page
        And I search for the client I'm interacting with
        And I click the clients details page link
        When I navigate to the clients report checklist page
        Then I can only see the 'public authority pfa high' specific section
