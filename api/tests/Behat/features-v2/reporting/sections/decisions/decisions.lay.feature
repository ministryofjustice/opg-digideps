@v2 @v2_reporting_1 @decisions
Feature: Decisions

    @lay-pfa-high-not-started
    Scenario: A user completes the decisions section
        Given a Lay Deputy has not started a report
        And I view the report overview page
        Then I should see "decisions" as "not started"
        When I view and start the decisions report section
        And I confirm that the clients mental capacity is the same
        And I confirm the clients last assessment date
        And I confirm that no significant decisions have been made for the client
        Then the decisions summary page should contain the details I entered
