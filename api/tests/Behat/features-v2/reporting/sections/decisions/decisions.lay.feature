@decisions @v2 @v2_reporting_1 @mia
Feature: Decisions

    @lay-health-welfare-not-started
    Scenario: A user has made no significant decisions
        Given a Lay Deputy has not started a Health and Welfare report
        When I view and start the decisions report section
        And select 'No' to the significant decisions
        And add an explanation in the text box
        Then the decisions summary page should contain the details I entered
