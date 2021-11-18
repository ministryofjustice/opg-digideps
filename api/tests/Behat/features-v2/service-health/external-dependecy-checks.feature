@service-health @v2_admin
Feature: Overview of external dependencies the application requires to function

    @infra @smoke
    Scenario: check maintenance page checks
        When I go to "/manage/availability"
        Then I should see "Sirius: OK"
        And I should see "Api: OK"
        And I should see "Redis: OK"
        And I should see "Notify: OK"
        And I should see "ClamAV: OK"
        And I should see "htmlToPdf: OK"
