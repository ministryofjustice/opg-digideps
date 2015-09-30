Feature: deputy / user / pre checks

    @deputy
    Scenario: check app status
        Given the application config is valid
        And I save the application status into "init"