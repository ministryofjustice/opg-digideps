@v2 @debts
Feature: Report debts

  Scenario: A user has no debts
    Given a Lay Deputy has not started a Pfa Low Assets report
    And I vists the report overview page
    Then I should see "debts" as "not started"
    When I view and start the debts report section
    And I have no debts
    Then I should see the expected debts section summary
    When I follow the link back to the report overview page
    Then I should see "debts" as "finished"

  Scenario: A user has some debts
    Given a Lay Deputy has not started a Pfa Low Assets report
    When I view and start the debts report section
    And I have a debt to add
    And I add some debt values
    And I say how the debts are being managed
    Then I should see the expected debts section summary
    When I follow link back to report overview page
    Then I should see "debts" as "finished"
