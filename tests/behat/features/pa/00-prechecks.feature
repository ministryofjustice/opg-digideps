Feature: PA pre checks

    Scenario: check app status
        Given the application config is valid
        When I go to admin page "/manage/availability"
        And the response status code should be 200
        