Feature: admin / pre checks

    @infra @smoke
    Scenario: check maintenance page checks
        When I go to admin page "/health-check/service"
        Then I should see "Api: OK"
        And I should see "Redis: OK"

    @infra @smoke
    Scenario: check maintenance page checks
        When I go to admin page "/health-check/dependencies"
        Then I should see "Notify: OK"
