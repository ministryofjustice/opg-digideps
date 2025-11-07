@v2 @v2_reporting_1 @report-submissions-ndr
Feature: Submitting a report - NDR

    @ndr-completed
    Scenario: When an NDR is submitted, I should see a lay high assets report for the next reporting period
        Given a Lay Deputy has a completed NDR report
        And all the reports for the first client are associated with a pfa court order
        And I visit the report overview page
        When I preview and check the report
        And I continue to declaration and submission
        And I confirm I agree to the declaration
        And I confirm I am the sole deputy
        And I submit my report
        Then my report should be submitted
        When all the reports for the first client are associated with a pfa court order
        When I visit the court order page
        Then I should see Lay High Assets report for the next reporting period
