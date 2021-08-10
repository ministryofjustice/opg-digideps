@v2 @admin-user-search @admin-user
Feature: Admin - User Search (same functionality for all admin types)

    @super-admin
    Scenario: A super admin user searches for a user
        Given a super admin user accesses the admin app
        And I have created the appropriate search test users
        When I search for one of the test users using partial name
        Then I should see the correct search results
        When I search for one of the test users using full name
        Then I should see the correct search results
        When I search for one of the test users with the Lay filter
        Then I should see the correct search results
        When I search for one of the test users with the Professional filter
        Then I should see the correct search results
        When I search for one of the test users with the Professional Named filter
        Then I should see the correct search results
        When I search for one of the test users with the Public Authority filter
        Then I should see the correct search results
        When I search for one of the test users with the Public Authority Named filter
        Then I should see the correct search results
        When I search for one of the test users with the Admin filter
        Then I should see the correct search results
        When I search for one of the test users with the Super Admin filter
        Then I should see the correct search results
        When I search for one of the test users with the All Roles filter
        Then I should see the correct search results
        When I search for one of the NDR test users with the All Roles filter
        Then I should see the correct search results
