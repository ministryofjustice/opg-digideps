@v2 @gifts
Feature: Gifts

  Scenario: A user has not donated any gifts
    Given a Lay Deputy has a new report
    And I view and start the gifts report section
    Then I should be on the gifts exist page
    When I choose no and save on gifts exist section
    Then I should be on the gifts summary page
    And I should see the expected gifts report section responses
    When I follow link back to report overview page
    Then I should be on the Lay reports overview page
    And I should see "gifts" as "no gifts"

  Scenario: A user has donated two gifts
    Given a Lay Deputy has a new report
    And I view and start the gifts report section
    Then I should be on the gifts exist page
    When I choose yes and save on gifts exist section
    Then I should be on the add a gift page
    When I fill in gift description and amount
    And I choose to save and add another
    Then I should be on the add a gift page
    When I fill in gift description and amount
    And I choose to save and continue
    Then I should be on the gifts summary page
    And I should see the expected gifts report section responses
    When I follow link back to report overview page
    Then I should be on the Lay reports overview page
    And I should see "gifts" as "2 gifts"

  Scenario: A user partially completes the gifts section and then edits their responses
    Given a Lay Deputy has a completed report
    And I go to the url for the report overview page
    Then I should see "gifts" as "no gifts"
    When I view the gifts report section
    Then I should be on the gifts summary page
    When I follow the edit link for whether gifts exist
    Then I should be on the gifts exist page
    When I choose yes and save on gifts exist section
    Then I should be on the add a gift page
    When I fill in gift description and amount
    And I choose to save and continue
    Then I should be on the gifts summary page
    And the previous section should be "Deputy expenses"
    And the next section should be "Money transfers"
    When I follow link back to report overview page
    Then I should be on the Lay reports overview page
    And I should see "gifts" as "1 gift"
    When I follow edit link for gifts section
    Then I should be on the gifts summary page
    When I follow edit link on first gift
    Then I should be on the edit a gift page
    When I edit first gift description and amount
    And I choose to save and continue
    Then I should see the expected gifts report section responses
    When I follow add a gift link
    Then I should be on the add a gift page
    When I fill in gift description and amount
    And I choose to save and continue
    Then I should be on the gifts summary page
    And I should see the expected gifts report section responses
    When I follow remove a gift link on second gift
    Then I should be on the delete a gift page
    When I confirm to remove gift
    Then I should be on the gifts summary page
    And I should see the expected gifts report section responses
    When I follow remove a gift link on first gift
    Then I should be on the delete a gift page
    When I confirm to remove gift
    Then I should be on the gifts start page
    When I follow link back to report overview page
    Then I should be on the Lay reports overview page
    And I should see "gifts" as "not started"
