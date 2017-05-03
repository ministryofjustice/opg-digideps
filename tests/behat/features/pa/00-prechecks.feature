Feature: PA pre checks

    Scenario: check app status
        Given the application config is valid
        And I save the application status into "init-pa"
        When I go to admin page "/manage/availability"
        And the response status code should be 200
        