@v2 @service-health @v2_admin
Feature: Overview of external dependencies the application requires to function

    @infra @smoke
    Scenario: check service health page checks
        When I go to "/health-check/service"
        And I should see "Api: OK"
        And I should see "Redis: OK"
        And I should see "ClamAV: OK"
        And I should see "htmlToPdf: OK"

    @infra @smoke
    Scenario: check dependency health page checks
        When I go to "/health-check/dependencies"
        Then I should see "Sirius: OK"
        And I should see "Notify: OK"
