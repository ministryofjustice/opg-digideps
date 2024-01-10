@v2
Feature: Org - A professional deputy adds admin and non-admin users to org

    @prof-admin-combined-high-not-started
    Scenario: A professional deputy with admin permissions can add new org users
        Given a Professional Admin Deputy has not Started a Combined High Assets report
        When I visit the organisation Add User page for the logged-in user
        Then I should be able to add a new user to the organisation
        
        
    @prof-team-hw-not-started
    Scenario: A professional deputy without admin permissions cannot add other org users
        Given a Professional Team Deputy has not started a health and welfare report
        And I navigate to my user settings page
        Then I can only view my user details
        And I visit the organisation Add User page for the logged-in user
        Then I should be redirected and denied access to continue
        
