@v2 @v2_reporting_1 @report-submissions @iqpal
Feature: Submitting a report - NDR

    @ndr-completed
    Scenario: Submitting a completed report
        Given a Lay Deputy has a completed NDR report
        And I visit the report overview page
        When I preview and check the report
        And I continue to declaration and submission
        And I confirm I agree to the declaration
        And I confirm I am the sole deputy
        And I submit my report
        Then my report should be submitted
        When I visit the lay start page
        Then I should see Lay High Assets report for the next reporting period
