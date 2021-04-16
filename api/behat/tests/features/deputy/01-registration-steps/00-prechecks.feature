Feature: pre checks

    @deputy @ndr @infra
    Scenario: check app status
        Given the deputy area works properly
        And the admin area works properly
        And I reset the behat SQL snapshots

    @infra @smoke
    Scenario: check maintenance page checks
      When I go to "/manage/availability"
      Then I should see "Sirius: OK"
      And I should see "Api: OK"
      And I should see "Redis: OK"
      And I should see "Notify: OK"
      And I should see "ClamAV: OK"
      And I should see "wkHtmlToPDf: OK"
