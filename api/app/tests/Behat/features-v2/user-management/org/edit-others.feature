@v2
Feature: Org - A professional deputy edits the details of other org users

    @prof-admin-combined-high-not-started
    Scenario: A professional deputy with admin permissions can edit other org user details
        Given the user has 'admin' permissions and another user exists within the same organisation
        When the user I'm interacting with logs in to the frontend of the app
        And I visit the organisation settings user account page for the logged in user
        And I click to edit the other org user
        When I edit the users account details 
        Then the user should be updated


    @prof-team-hw-not-started
    Scenario: A professional team deputy without admin permissions cannot edit users from org
        Given the user has 'no admin' permissions and another user exists within the same organisation
        When the user I'm interacting with logs in to the frontend of the app
        And I visit the organisation settings user account page for the logged in user
        Then I can view the other org user but I cannot 'Edit' them
