@v2
Feature: Org - A professional deputy deletes users from org

    @prof-admin-combined-high-not-started
    Scenario: A professional deputy with admin permissions can delete users from org
        Given the user has 'admin' permissions and another user exists within the same organisation
        When the user I'm interacting with logs in to the frontend of the app
        And I visit the organisation settings page for the logged in user
        And I attempt to remove an org user 
        Then the the user should be deleted

    @prof-team-hw-not-started
    Scenario: A professional team deputy without admin permissions cannot delete users from org
        Given the user has 'no admin' permissions and another user exists within the same organisation
        When the user I'm interacting with logs in to the frontend of the app
        And I visit the organisation settings page for the logged in user
        Then I can view the other org user but I can't delete them
