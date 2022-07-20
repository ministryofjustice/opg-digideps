@v2 @registration @activate @v2_admin
Feature: Activate user account - Organisation users

    @admin
    Scenario: User registration date and active flag are only set when a user completes the full registration flow - Case manager created
        Given an admin user accesses the admin app
        And a case manager creates an org user
        When the user clicks the activate account link in their email
        And they complete all except the last step in the registration flow
        And an admin user accesses the admin app
        Then the partially registered users 'active flag' should 'not be' set
        And the partially registered users 'registration date' should 'not be' set
        When the user completes the final registration step
        And an admin user accesses the admin app
        Then the partially registered users 'active flag' should 'be' set
        And the partially registered users 'registration date' should 'be' set

    @prof-admin-health-welfare-not-started @admin
    Scenario: User registration date and active flag are only set when a user completes the full registration flow - Org admin created
        Given a Professional Admin Deputy has not started a report
        When the admin user invites a new user to their organisation
        And the user clicks the activate account link in their email
        And they complete all except the last step in the registration flow
        And an admin user accesses the admin app
        Then the partially registered users 'active flag' should 'not be' set
        And the partially registered users 'registration date' should 'not be' set
        When the user completes the final registration step
        And an admin user accesses the admin app
        Then the partially registered users 'active flag' should 'be' set
        And the partially registered users 'registration date' should 'be' set
