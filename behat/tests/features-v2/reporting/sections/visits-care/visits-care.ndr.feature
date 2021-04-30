@v2 @visits-care.ndr
Feature: Visits and Care (NDR)

  Scenario: A user lives with a client that doesn't receive paid care, doesn't have a care plan and has no plans to move.
    Given a Lay Deputy has not started an NDR report
    And I view and start the visits and care report section
    When I confirm I live with the client
    When I confirm the client does not receive paid care
    When I provide details on who is doing the caring
    When I confirm the client does not have a care plan
    When I confirm there are no plans to move the client to a new residence section
    And I should see the expected visits and care report section responses
    When I follow link back to report overview page
    Then I should be on the Lay reports overview page
    And I should see "visits-care" as "finished"

  Scenario: A user does not live with a client that receives paid care, has a care plan and has plans to move.
    Given a Lay Deputy has not started an NDR report
    And I view and start the visits and care report section
    When I confirm I do not live with the client
    When I confirm the client receives paid care which is fully funded by someone else
    When I provide details on who is doing the caring
    When I confirm the client has a care plan
    When I confirm there are plans to to move the client to a new residence section
    And I should see the expected visits and care report section responses
    When I follow link back to report overview page
    Then I should be on the Lay reports overview page
    And I should see "visits-care" as "finished"

  Scenario: A user partially completes the section and then edits their responses
    Given a Lay Deputy has not started an NDR report
    And I view the report overview page
    Then I should see "visits-care" as "not started"
    When I view and start the visits and care report section
    Then I confirm I live with the client
    When I confirm the client receives paid care which is fully funded by someone else
    And I press report sub section back button
    When I confirm the client receives paid care which is partially funded by someone else
    And I press report sub section back button
    And I press report sub section back button
    And I press report sub section back button
    When I follow link back to report overview page
    Then I should see "visits-care" as "not finished"
    When I view the visits and care report section
    When I follow edit link for does client receive paid care page
    And I confirm the client receives paid care which is funded by themselves
