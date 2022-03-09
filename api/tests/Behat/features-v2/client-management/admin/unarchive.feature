@v2 @v2_admin @admin-client-unarchive
Feature: Admin - Client Unarchived

    @super-admin @prof-team-hw-not-started
    Scenario: A super admin user can unarchived a client
        Given a Professional Team Deputy has not started a health and welfare report
        Given a super admin user accesses the admin app
        And an org deputy has an archived client
        When I visit the admin client archived page
        And I attempt to unarchived the client
        Then the client should be unarchived

    @admin-manager @prof-team-hw-not-started
    Scenario: An admin manager user can unarchived a client
        Given a Professional Team Deputy has not started a health and welfare report
        Given an admin manager user accesses the admin app
        And an org deputy has an archived client
        When I visit the admin client archived page
        And I attempt to unarchived the client
        Then the client should be unarchived

    @admin @prof-team-hw-not-started
    Scenario: An admin user cannot unarchived a client
        Given a Professional Team Deputy has not started a health and welfare report
        Given an admin user accesses the admin app
        And an org deputy has an archived client
        When I visit the admin client archived page
        And I attempt to unarchived the client
        Then the client should not be unarchived
