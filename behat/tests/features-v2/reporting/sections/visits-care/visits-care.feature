@v2 @visits-care
Feature: Visits and Care

  Scenario: A user lives with a client that doesn't receive paid care and doesn't have a care plan.
    Given a Lay Deputy has not started a report
    And I view and start the visits and care report section
    Then I should be on the live with client page
    When I choose yes and save on the live with the client section
    Then I should be on the client receive paid care page
    When I choose no and save on the client receive paid care section
    Then I should be on the who is doing the caring page
    When I fill out and save the who is doing caring section
    Then I should be on the does the client have a care plan page
    When I choose no and save on the client has care plan section
    Then I should be on the visits and care report summary page
    And I should see the expected visits and care report section responses
    And I should see all the additional information I gave for visit and care
    And the previous section should be "Contacts"
    And the next section should be "Bank accounts"
    When I follow link back to report overview page
    Then I should be on the Lay reports overview page
    And I should see "visits-care" as "finished"

  Scenario: A user lives with a client that doesn't receive paid care and has a care plan.
    Given a Lay Deputy has not started a report
    And I view and start the visits and care report section
    Then I should be on the live with client page
    When I choose yes and save on the live with the client section
    Then I should be on the client receive paid care page
    When I choose no and save on the client receive paid care section
    Then I should be on the who is doing the caring page
    When I fill out and save the who is doing caring section
    Then I should be on the does the client have a care plan page
    When I choose yes and save on the client has care plan section
    Then I should be on the visits and care report summary page
    And I should see the expected visits and care report section responses
    And I should see all the additional information I gave for visit and care
    And the previous section should be "Contacts"
    And the next section should be "Bank accounts"
    When I follow link back to report overview page
    Then I should be on the Lay reports overview page
    And I should see "visits-care" as "finished"

  Scenario: A user lives with a client that receives paid care and has a care plan.
    Given a Lay Deputy has not started a report
    And I view and start the visits and care report section
    Then I should be on the live with client page
    When I choose yes and save on the live with the client section
    Then I should be on the client receive paid care page
    When I choose yes and save on the client receive paid care section
    Then I should be on the who is doing the caring page
    When I fill out and save the who is doing caring section
    Then I should be on the does the client have a care plan page
    When I choose yes and save on the client has care plan section
    Then I should be on the visits and care report summary page
    And I should see the expected visits and care report section responses
    And I should see all the additional information I gave for visit and care
    And the previous section should be "Contacts"
    And the next section should be "Bank accounts"
    When I follow link back to report overview page
    Then I should be on the Lay reports overview page
    And I should see "visits-care" as "finished"

  Scenario: A user does not live with a client that receives paid care and has a care plan.
    Given a Lay Deputy has not started a report
    And I view and start the visits and care report section
    Then I should be on the live with client page
    When I choose no and save on the live with the client section
    Then I should be on the client receive paid care page
    When I choose yes and save on the client receive paid care section
    Then I should be on the who is doing the caring page
    When I fill out and save the who is doing caring section
    Then I should be on the does the client have a care plan page
    When I choose yes and save on the client has care plan section
    Then I should be on the visits and care report summary page
    And I should see the expected visits and care report section responses
    And I should see all the additional information I gave for visit and care
    And the previous section should be "Contacts"
    And the next section should be "Bank accounts"
    When I follow link back to report overview page
    Then I should be on the Lay reports overview page
    And I should see "visits-care" as "finished"

  Scenario: A user partially completes the section and then edits their responses
    Given a Lay Deputy has not started a report
    And I view the report overview page
    Then I should see "visits-care" as "not started"
    When I view and start the visits and care report section
    Then I choose yes and save on the live with the client section
    And I press report sub section back button
    And I press report sub section back button
    Then I should see text asking to answer the question
    When I follow link back to report overview page
    Then I should see "visits-care" as "not finished"
    When I view the visits and care report section
    Then I should be on the visits and care report summary page
    When I follow edit link for does client receive paid care page
    And I choose no and save on the client receive paid care section
    And I follow link back to report overview page
    Then I should see "visits-care" as "finished"
