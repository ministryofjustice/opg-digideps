@v2 @v2_admin @app-notification @mia
Feature: Restricting visibility of banner notification to super admin only
    As a super admin user
    To ensure that I am aware of the version of the app I am using
    I want to confirmation of this with a banner notification

    @super-admin
    Scenario: A super admin has visibility of the banner notification in the admin app
        Given a super admin user accesses the admin app
        Then I should see a banner confirming the version of the app I am using

    @admin-manager
    Scenario: An admin manager does not have visibility of the banner notification
        Given an admin manager user accesses the admin app
        Then I should not see the banner confirming the version of the app I am using

    @lay-pfa-high-not-started
    Scenario: A Lay Deputy does not have visibility of the banner notification
        Given a Lay Deputy has not started a report
        And I visit the report overview page
        Then I should not see the banner confirming the version of the app I am using
