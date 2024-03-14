@v2 @v2_reporting_1 @report-submissions
Feature: Submitting a report

    @lay-pfa-high-completed
    Scenario: Submitting a completed report
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        When I preview and check the report
        And I continue to declaration and submission
        And I confirm I agree to the declaration
        And I confirm I am the sole deputy
        And I submit my report
        Then my report should be submitted

    @lay-pfa-high-completed
    Scenario: Deputy is ready to submit report but has a missing document in S3 and is redirected to re-upload page to re-upload document and submit report
        Given a Lay Deputy has a completed report
        When I visit the report overview page
        And I view the documents report section
        And I have documents to upload
        Then I attach a supporting document "test-image.png" to the report
        And the supporting document has expired and is no longer stored in the S3 bucket
        And I try to submit my report with the expired document
        Then I should be redirected to the re-upload page
        When I delete the missing document and re-upload "test-image.png" to the report
        And I preview and check the report
        And I continue to declaration and submission
        And I confirm I agree to the declaration
        And I confirm I am the sole deputy
        And I submit my report
        Then my report should be submitted
