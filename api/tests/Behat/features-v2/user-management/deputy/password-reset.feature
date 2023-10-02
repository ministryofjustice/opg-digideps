@v2
Feature: Deputy attempts to reset their password

    @lay-health-welfare-not-started
    Scenario: Deputy successfully resets their password
        Given a Lay Deputy exists
        When the user visits the forgotten your password page
        And the user sends a request to reset their password
        And the user clicks on the registration link sent to their email which has an 'active' token
        And the user successfully resets their password
        Then the user I'm interacting with logs in to the frontend of the app


    @lay-health-welfare-not-started
    Scenario: A deputy is unable to reset their password if the registration link has expired
        Given a Lay Deputy exists
        When the user visits the forgotten your password page
        And the user sends a request to reset their password
        And the user clicks on the registration link sent to their email which has an 'expired' token
        Then the password reset page should be expired


    @lay-health-welfare-not-started
    Scenario: Deputy unsuccessfully resets their password due to invalid token
        Given a Lay Deputy exists
        When the user visits the forgotten your password page
        And the user sends a request to reset their password
        When the user visits an invalid password reset url
        Then a password reset error should be thrown to the user
