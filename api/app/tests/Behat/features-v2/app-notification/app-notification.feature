@v2 @v2_sequential_3 @app-notification
Feature: App Notification - An admin can add and remove app notification for deputies

    @super-admin
    Scenario: An admin turns on an service notification with a message
        Given a super admin user accesses the admin app
        When I visit the service notification page
        And I set a service notification
        Then I should see the service message on the client login page

    @super-admin
    Scenario: An admin turns off an service notification after setting one
        Given a super admin user accesses the admin app
        When I visit the service notification page
        And I set a service notification and see it on the login page
        Then I turn off the service notification and can no longer see it on the client login page

    @super-admin
    Scenario: An admin trys to set a service notification without a message
        Given a super admin user accesses the admin app
        When I visit the service notification page
        And I set a service notification without a message
        Then I should see a validation error
