Feature: pre checks

    @deputy
    Scenario: check app status
        Given the deputy area works properly
        And the admin area works properly
        And I reset the behat SQL snapshots
        And I save the application status into "init"
