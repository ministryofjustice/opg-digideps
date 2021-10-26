@v2 @v2_reporting_2 @decisions
Feature: Decisions

    @lay-combined-high-not-started
    Scenario: The clients mental capacity has changed and no major decisions made
        Given a Lay Deputy has not started a Combined High Assets report
        Then I should see "decisions" as "not started"
        When I navigate to and start the decisions section
        And I confirm the clients mental capacity has changed and provide a reason
        And I confirm the date their mental capacity was last assessed
        And I confirm I have not made any significant decisions and provide a reason
        Then I should see the expected decisions report section responses on the summary page
        When I visit the report overview page
        Then I should see "decisions" as "finished"

    @lay-combined-high-not-started
    Scenario: The clients mental capacity has stayed the same and a major decision has been made
        Given a Lay Deputy has not started a Combined High Assets report
        When I navigate to and start the decisions section
        And I confirm the clients mental capacity has not changed
        And I confirm the date their mental capacity was last assessed
        And I confirm I have made a significant decisions
        And I provide details of 2 decisions I have made
        And I have no further decisions to declare
        Then I should see the expected decisions report section responses on the summary page
        When I visit the report overview page
        Then I should see "decisions" as "finished"

    @lay-combined-high-completed
    Scenario: I add a new decision I have made from the summary page
        Given a Lay Deputy has completed a Combined High Assets report
        When I visit the decisions summary page
        And I add 1 decisions I have made from the summary page
        And I have no further decisions to declare
        Then I should see the expected decisions report section responses on the summary page

    @lay-combined-high-completed
    Scenario: I edit an existing decision I have made from the summary page
        Given a Lay Deputy has completed a Combined High Assets report
        When I visit the decisions summary page
        And I edit a decision I have made from the summary page
        Then I should see the expected decisions report section responses on the summary page

    @lay-combined-high-completed
    Scenario: I remove an existing decision I have made from the summary page
        Given a Lay Deputy has completed a Combined High Assets report
        When I visit the decisions summary page
        And I remove a decision I have made from the summary page
        Then I should see the expected decisions report section responses on the summary page

    @lay-combined-high-not-started
    Scenario: I don't provide enough details when completing the decisions section
        Given a Lay Deputy has not started a Combined High Assets report
        When I navigate to and start the decisions section
        And I confirm the clients mental capacity has changed and don't provide a reason
        Then I should see a 'missing reason' validation error
        When I confirm the clients mental capacity has changed and provide a reason
        And I dont provide a date their mental capacity was last assessed
        Then I should see a 'missing date' validation error
        When I confirm the date their mental capacity was last assessed
        And I confirm I have not made any significant decisions and don't provide a reason
        Then I should see a 'missing reason' validation error
        When I confirm I have made a significant decisions
        And I don't provide details of the decision
        Then I should see a 'missing details' validation error
        When I visit the report overview page
        Then I should see "decisions" as "not finished"
