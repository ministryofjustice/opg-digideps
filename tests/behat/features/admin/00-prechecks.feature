Feature: admin / pre checks

    @infra @reset-emails
    Scenario: check app status
        Given the admin area works properly
        And the response status code should be 200
