@v2 @v2_sequential_1 @registration @ingest
Feature: Lay CSV data ingestion - sirius source data

    @super-admin
    Scenario: Uploading a Lay CSV that contains new pre-registration entities only
        Given a csv has been uploaded to the sirius bucket with the file 'lay-4-valid-rows.csv'
        When I run the lay CSV command the file contains 4 new pre-registration entities
        Then the new 'lay' entities should be added to the database
        And the count of the new 'lay' entities added should be in the command output

    @super-admin
    Scenario: Uploading a Lay CSV that contains existing pre-registration entities with new report type
        Given a csv has been uploaded to the sirius bucket with the file 'lay-1-row-updated-report-type.csv'
        When I run the lay CSV command where a file has a new report type '103' for case number '34343434'
        Then the clients report type should be updated

    @super-admin
    Scenario: Uploading a Lay CSV that contains deputies with missing required information alongside valid deputy rows
        Given a csv has been uploaded to the sirius bucket with the file 'lay-1-row-missing-all-required-1-valid-row.csv'
        When I run the lay CSV command the file has 1 row with missing values for 'caseNumber, clientLastname, deputyUid and deputySurname' and 1 valid row
        Then the new 'lay' entities should be added to the database
        And the count of the new 'lay' entities added should be in the command output

    @super-admin
    Scenario: Uploading a Lay CSV that contains contains a row with an invalid report type
        Given a csv has been uploaded to the sirius bucket with the file 'lay-1-row-invalid-report-type-1-valid-row.csv'
        When I run the lay CSV command the file has 1 row with an invalid report type and 1 valid row
        Then the new 'lay' entities should be added to the database
        And the count of the new 'lay' entities added should be in the command output

# Needs further rewrite so we're gracefully handling missing columns & not just stopping the process.
# Currently throws critical error
#    @super-admin
#    Scenario: Uploading a Lay CSV that has missing required columns
#        Given I save the application status into 'csv-processing'
#        When I upload a 'lay' CSV that does not have any of the required columns
#        Then I should see an error showing which columns are missing on the 'lay' csv upload page
#        Then I load the application status from 'csv-processing'
