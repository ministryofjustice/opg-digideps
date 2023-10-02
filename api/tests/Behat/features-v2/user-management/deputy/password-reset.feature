@v2
Feature: Deputy attempts to reset their password

    @lay-health-welfare-not-started
    Scenario: Deputy successfully resets their password
        Given a Lay Deputy exists
        When the user visits the forgotten your password page
        And the user sends a request to reset their password
        And successfully resets their password via the registration link sent to their email
        Then the user I'm interacting with logs in to the frontend of the app
