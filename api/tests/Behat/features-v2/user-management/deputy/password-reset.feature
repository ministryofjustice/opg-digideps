@v2
Feature: Deputy attempts to reset their password

    @lay-health-welfare-not-started
    Scenario: Deputy successfully resets their password
        Given a Lay Deputy exists
        When the user visits the forgotten your password page
        And the user sends a request to reset their password
        And successfully resets their password via the registration link sent to their email
        Then the user I'm interacting with logs in to the frontend of the app


    @lay-health-welfare-not-started
    Scenario: Deputy unsuccessfully resets their password due to invalid token
        Given a Lay Deputy exists
        When the user visits the forgotten your password page
        And the user sends a request to reset their password
        When the user visits an invalid password reset url
        Then a password reset error should be thrown to the user
