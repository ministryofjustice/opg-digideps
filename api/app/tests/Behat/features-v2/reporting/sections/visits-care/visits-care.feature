@v2 @v2_reporting_1 @visits-care
Feature: Visits and Care

@lay-pfa-high-not-started
  Scenario: A user lives with a client that doesn't receive paid care and doesn't have a care plan.
    Given a Lay Deputy has not started a report
    And I view and start the visits and care report section
    When I confirm I live with the client
    And I confirm the client does not receive paid care
    And I provide details on who is doing the caring
    And I confirm the client does not have a care plan
    Then I should see the expected visits and care report section responses
    When I follow link back to report overview page
    Then I should be on the Lay reports overview page
    And I should see "visits-care" as "finished"

@lay-pfa-high-not-started
  Scenario: A user does not live with a client that receives paid care and has a care plan.
    Given a Lay Deputy has not started a report
    And I view and start the visits and care report section
    When I confirm I do not live with the client
    And I confirm the client receives paid care which is fully funded by someone else
    And I provide details on who is doing the caring
    And I confirm the client has a care plan
    Then I should see the expected visits and care report section responses
    When I follow link back to report overview page
    Then I should be on the Lay reports overview page
    And I should see "visits-care" as "finished"

@lay-pfa-high-not-started
  Scenario: A user partially completes the section and then edits their responses
    Given a Lay Deputy has not started a report
    And I view the report overview page
    Then I should see "visits-care" as "not started"
    When I view and start the visits and care report section
    And I confirm I live with the client
    And I confirm the client receives paid care which is fully funded by someone else
    And I press report sub section back button
    And I confirm the client receives paid care which is partially funded by someone else
    And I press report sub section back button
    And I press report sub section back button
    And I press report sub section back button
    And I follow link back to report overview page
    Then I should see "visits-care" as "not finished"
    When I view the visits and care report section
    And I follow edit link for does client receive paid care page
    And I confirm the client receives paid care which is funded by themselves
    Then I should be on the visits and care report summary page
