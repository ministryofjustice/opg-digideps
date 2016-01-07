Feature: deputy / user / pre checks

    @deputy
    Scenario: check app status
        Given the application config is valid
        And I save the application status into "init"
        When I go to "/manage/availability"
        Then print last response
        And the response status code should be 200