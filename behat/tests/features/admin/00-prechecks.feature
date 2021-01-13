Feature: admin / pre checks

  @infra @smoke
  Scenario: check maintenance page checks
    When I go to admin page "/manage/availability"
    Then I should see "Api: OK"
    And I should see "Redis: OK"
    And I should see "Notify: OK"
