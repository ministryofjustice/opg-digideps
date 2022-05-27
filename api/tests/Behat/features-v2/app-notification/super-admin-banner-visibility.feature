@v2 @v2_admin @app-notification
Feature: Limiting visibility of banner notification to super admin only
    As a super admin user
    To ensure that I am aware of the version of the app I am using
    I want confirmation of this via a banner notification

    @super-admin
    Scenario: A super admin has visibility of the banner notification in the admin app
        Given a super admin user accesses the admin app
        Then I should see the banner confirming the version of the app I am using

    @admin-manager
    Scenario: An admin manager does not have visibility of the banner notification
        Given an admin manager user accesses the admin app
        Then I should not see the banner confirming the version of the app I am using


    @super-admin
    Scenario: A super admin has visibility of the banner notification in production
        Given a super admin user accesses the live app
        Then I should see the banner confirming the version of the app I am using

    @lay-pfa-high-completed @prof-admin-health-welfare-completed
    Scenario: A Lay Deputy does not have visibility of the banner notification in production
        Given a Professional Deputy has completed a Pfa Low Assets report
        Then I should not see the banner confirming the version of the app I am using
