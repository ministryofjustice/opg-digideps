@v2 @registration @self-register @v2_admin
Feature: Lay Deputy Self Registration

    @super-admin @admin
    Scenario: A Lay user with an existing pre-registration record can self register
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload a lay CSV that contains 3 new pre-registration entities
        And a Lay Deputy registers to deputise for a client with valid details
        Then I should be on the Lay homepage
