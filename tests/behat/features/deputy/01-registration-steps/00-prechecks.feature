Feature: pre checks

    @deputy
    Scenario: check app status
        Given the application config is valid
        And I reset the behat SQL snapshots
        And I save the application status into "init"
        When I go to "/manage/availability"
