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
        And I confirm that 'No' significant decisions have been made for the client
        Then the decisions summary page should contain the details I entered

    @lay-pfa-high-not-started
    Scenario: A user edits their response to the significant decisions question
        Given a Lay Deputy has not started a report
        And I view the report overview page
        Then I should see "decisions" as "not started"
        When I view and start the decisions report section
        And I confirm that the clients mental capacity is the same
        And I confirm the clients last assessment date
        And I confirm that 'No' significant decisions have been made for the client
        Then the decisions summary page should contain the details I entered
        Given I edit my response to the significant decisions question to 'Yes'
        And I add the details of the decision as requested
        And I confirm that 'no' additional significant decisions have been made for the client
        Then the decisions summary page should reflect the updated details I entered

    @lay-pfa-high-not-started
    Scenario: A user adds 2 decisions using add another option
        Given a Lay Deputy has not started a report
        And I view the report overview page
        Then I should see "decisions" as "not started"
        When I view and start the decisions report section
        And I confirm that the clients mental capacity is the same
        And I confirm the clients last assessment date
        And I confirm that 'Yes' significant decisions have been made for the client
        And I add the details of the decision as requested
        And I confirm that 'yes' additional significant decisions have been made for the client
        Then I should be on the clients add decision page
        And I add the details of the additional decision as requested
        And I confirm that 'no' additional significant decisions have been made for the client
        Then the decisions summary page should reflect the updated details I entered

    @lay-pfa-high-not-started
    Scenario: A user adds another decision through editing an existing decision
        Given a Lay Deputy has not started a report
        And I view the report overview page
        Then I should see "decisions" as "not started"
        When I view and start the decisions report section
        And I confirm that the clients mental capacity is the same
        And I confirm the clients last assessment date
        And I confirm that 'Yes' significant decisions have been made for the client
        And I add the details of the decision as requested
        And I confirm that 'no' additional significant decisions have been made for the client
        Then the decisions summary page should reflect the updated details I entered
        Then I edit an existing decision
        Then I should be on the clients edit decision page
        When I click save and continue
        Then the decisions summary page should reflect the updated details I entered
