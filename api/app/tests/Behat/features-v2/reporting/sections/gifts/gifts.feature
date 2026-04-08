@v2 @v2_reporting_1 @gifts
Feature: Gifts

@lay-pfa-high-not-started
  Scenario: A user has not donated any gifts
    Given a Lay Deputy has not started a report
    When I view and start the gifts report section
    And I have not given any gifts
    Then I should see the expected gifts report section responses
    When I follow link back to report overview page
    Then I should see "gifts" as "no gifts"

@lay-pfa-high-not-started
  Scenario: A user has donated multiple gifts
    Given a Lay Deputy has not started a report
    When I view and start the gifts report section
    And I have given multiple gifts
    Then I should see the expected gifts report section responses
    When I follow link back to report overview page
    Then I should see "gifts" as "2 gifts"

@lay-pfa-high-completed
  Scenario: A user changes their mind and declares a gift
    Given a Lay Deputy has a completed report
    And I view the report overview page
    Then I should see "gifts" as "no gifts"
    When I change my mind and declare a gift
    Then I should see the expected gifts report section responses
    When I follow link back to report overview page
    Then I should see "gifts" as "1 gift"

@lay-pfa-high-completed
  Scenario: A user edits a gift
    Given a Lay Deputy has a completed report
    When I edit an existing gift
    Then I should see the expected gifts report section responses

@lay-pfa-high-not-started
  Scenario: A user adds and deletes gifts
    Given a Lay Deputy has not started a report
    When I view and start the gifts report section
    And I have given multiple gifts
    Then I should see the expected gifts report section responses
    When I remove the second gift
    Then I should see the expected gifts report section responses
    When I remove the first gift
    And I follow link back to report overview page
    Then I should see "gifts" as "not started"
