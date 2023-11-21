@v2 @v2_reporting_1 @actions
Feature: Actions

    @lay-pfa-high-not-started
    Scenario: A user has made no financial decisions and has no concerns
        Given a Lay Deputy has not started a report
        And I view and start the actions report section
        Then I should be on the financial decision actions page
        When I choose no and save on financial decision actions section
        Then I should be on the concerns actions page
        When I choose no and save on concerns actions section
        Then I should be on the actions report summary page
        And I should see the expected action report section responses
        When I follow link back to report overview page
        Then I should be on the Lay reports overview page
        And I should see "actions" as "finished"

    @lay-pfa-high-not-started
    Scenario: A user has made financial decisions and has concerns
        Given a Lay Deputy has not started a report
        And I view and start the actions report section
        Then I should be on the financial decision actions page
        When I choose yes and save on financial decision actions section
        Then I should be on the concerns actions page
        When I choose yes and save on concerns actions section
        Then I should be on the actions report summary page
        And I should see the expected action report section responses

    @lay-pfa-high-not-started
    Scenario: A user partially completes the section and then edits their responses
        Given a Lay Deputy has not started a report
        And I view the report overview page
        Then I should see "actions" as "not started"
        When I view and start the actions report section
        Then I choose no and save on financial decision actions section
        And I press report sub section back button
        And I press report sub section back button
        Then I should see text asking to answer the question
        When I follow link back to report overview page
        Then I should see "actions" as "not finished"
        When I view the actions report section
        Then I should be on the actions report summary page
        When I follow edit link on concerns question
        And I choose no and save on concerns actions section
        And I follow link back to report overview page
        Then I should see "actions" as "finished"
