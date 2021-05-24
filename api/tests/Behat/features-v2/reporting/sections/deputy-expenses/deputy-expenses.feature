@v2 @expenses
Feature: Deputy expenses - Applies to Lays with combined/PFA reports only

    Scenario: A user has no supporting expenses to declare
        Given a Lay Deputy has not started a report
        When I navigate to the deputy expenses report section
        And I have no expenses to declare
        Then the expenses summary page should contain the details I entered

    Scenario: A user has a single expense to declare
        Given a Lay Deputy has not started a report
        When I view and start the deputy expenses report section
        And I have expenses to declare
        And I enter valid expense details
        And there are no further expenses to add
        Then the expenses summary page should contain the details I entered

    Scenario: A user has multiple expenses to declare
        Given a Lay Deputy has not started a report
        When I view and start the deputy expenses report section
        And I have expenses to declare
        And I enter valid expense details
        And I declare another expense
        And there are no further expenses to add
        Then the expenses summary page should contain the details I entered

    Scenario: A user submits invalid expense data
        Given a Lay Deputy has not started a report
        When I view and start the deputy expenses report section
        And I have expenses to declare
        And I don't enter any values
        Then I should see 'missing values' errors
        And I enter the wrong type of values
        Then I should see 'type validation' errors
        And I enter an expense amount that is too high
        Then I should see an 'amount out of range' error
        And I enter an expense amount that is too low
        Then I should see an 'amount out of range' error

    @acs
    Scenario: A user edits expense data they have submitted
        Given a Lay Deputy has not started a report
        When I view and start the deputy expenses report section
        And I have expenses to declare
        And I enter valid expense details
        And I declare another expense
        And there are no further expenses to add
        And I edit the expense details
        Then the expenses summary page should contain the details I entered

    Scenario: A user removes expense data they have submitted
        Given a Lay Deputy has not started a report
        When I view and start the deputy expenses report section
        And I have expenses to declare
        And I enter valid expense details
        And I declare another expense
        And there are no further expenses to add
        And I remove an expense I declared
        Then the expenses summary page should contain the details I entered

    Scenario: A user adds expense data from the expenses summary page
        Given a Lay Deputy has not started a report
        When I view and start the deputy expenses report section
        And I have expenses to declare
        And I enter valid expense details
        And I declare another expense
        And there are no further expenses to add
        And I add an expense from the expense summary page
        Then the expenses summary page should contain the details I entered

    Scenario: A user adds expense data and then changes their mind
        Given a Lay Deputy has not started a report
        When I view and start the deputy expenses report section
        And I have expenses to declare
        And I enter valid expense details
        And there are no further expenses to add
        And I change my mind and answer no to expenses to declare
        Then the expenses summary page should contain the details I entered
