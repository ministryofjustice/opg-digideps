@v2 @v2_sequential_1 @registration @activate
Feature: Activate user account - Lay Users

    @super-admin @admin
    Scenario: User registration date and active flag are only set when a user completes the full registration flow - case manager created
        Given pre-registration details exist to allow a lay deputy to register for the service
        And a case manager accesses the admin app
        And they create a 'lay' user with name details that match the pre-registration details
        When the user clicks the activate account link in their email
        And they complete all except the last step in the registration flow
        And a case manager accesses the admin app
        Then the partially registered users 'active flag' should 'not be' set
        And the partially registered users 'registration date' should 'not be' set
        When the user completes the final registration step
        Then I should be on the Lay homepage
        And an admin user accesses the admin app
        Then the partially registered users 'active flag' should 'be' set
        And the partially registered users 'registration date' should 'be' set

    @super-admin @admin
    Scenario: User registration date and active flag are only set when a user completes the full registration flow - self registration
        Given pre-registration details exist to allow a lay deputy to register for the service
        When a lay deputy provides details that match the pre-registration details
        And the user clicks the activate account link in their email
        And they complete all except the last step in the registration flow
        And a case manager accesses the admin app
        Then the partially registered users 'active flag' should 'not be' set
        And the partially registered users 'registration date' should 'not be' set
        When the user completes the final registration step
        Then I should be on the Lay homepage
        And an admin user accesses the admin app
        Then the partially registered users 'active flag' should 'be' set
        And the partially registered users 'registration date' should 'be' set

    @super-admin @admin
    Scenario: A Lay user registers for the service using unicode characters - case manager created
        Given pre-registration details exist with no unicode characters
        And a case manager accesses the admin app
        And they create a 'lay' user with name details that match the pre-registration details
        When the user clicks the activate account link in their email
        And they complete the user registration flow using unicode characters
        Then I should be on the Lay homepage
