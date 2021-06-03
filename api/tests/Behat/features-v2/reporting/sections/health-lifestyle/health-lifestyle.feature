@v2 @lifestyle
Feature: Health and Lifestyle (Lay / PA / Prof share same functionality)

    @lay-health-welfare-not-started
    Scenario: A user skips both sections
        Given a Lay Deputy has not started a Health and Welfare report
        And I view and start the health and lifestyle report section
        When I skip both lifestyle sections
        Then I should see the expected lifestyle section summary
        When I visit the report overview page
        Then I should see "lifestyle" as "not started"

    @lay-health-welfare-not-started
    Scenario: A user states client takes no part in social or leisure activities
        Given a Lay Deputy has not started a Health and Welfare report
        And I view and start the health and lifestyle report section
        When I fill in details about clients health and care appointments
        And I confirm that client takes part in no leisure or social activities
        Then I should see the expected lifestyle section summary

    @lay-health-welfare-not-started
    Scenario: A user states client takes part in social or leisure activities
        Given a Lay Deputy has not started a Health and Welfare report
        And I view and start the health and lifestyle report section
        When I fill in details about clients health and care appointments
        And I confirm that client takes part in leisure and social activities
        Then I should see the expected lifestyle section summary
        When I visit the report overview page
        Then I should see "lifestyle" as "finished"

    @lay-health-welfare-completed
    Scenario: A user edits existing answers
        Given a Lay Deputy has completed a Health and Welfare report
        And I visit the health and lifestyle summary section
        When I edit the lifestyle section answers as client takes part in activities
        Then I should see the expected lifestyle section summary
        When I edit the lifestyle section answers as client doesn't take part in activities
        Then I should see the expected lifestyle section summary

    @lay-health-welfare-not-started
    Scenario: A user tries to get through sections without filling in relevant fields
        Given a Lay Deputy has not started a Health and Welfare report
        And I view and start the health and lifestyle report section
        When I do not enter any appointment details
        Then I receive the expected lifestyle appointments validation message
        When I do not enter any leisure and activity details
        Then I receive the expected lifestyle activities validation message
