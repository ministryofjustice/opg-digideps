Feature: admin / pre checks

    @infra
    Scenario: check app status
        Given the admin area works properly
        And the response status code should be 200
        And I save the application status into "admin-init"
