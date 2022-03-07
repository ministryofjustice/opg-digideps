@v2 @v2_admin @admin-client-unarchive
Feature: Admin - Client Unarchived

    @super-admin @prof-named-pfa-high-not-started @mia
    Scenario: A super admin user can unarchive a client
        Given a super admin user accesses the admin app
        When I visit the admin client archived page
        And I attempt to unarchive the client
        Then the client should be unarchived

#    @admin-manager @prof-named-pfa-high-not-started
#    Scenario: An admin manager user can unarchive a client
#        Given an admin manager user accesses the admin app
#        When I visit the admin client details page for an existing client linked to a Lay deputy
#        And I attempt to discharge the client
#        Then the client should be discharged
#
#    @admin @prof-named-pfa-high-not-started
#    Scenario: An admin user cannot unarchive a client
#        Given an admin user accesses the admin app
#        When I visit the admin client details page for an existing client linked to a Lay deputy
#        And I attempt to discharge the client
#        Then the client should not be discharged
