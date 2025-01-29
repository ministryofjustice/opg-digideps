@v2 @v2_sequential_1 @registration @ingest @multiclient
Feature: Lay CSV data ingestion - sirius source data for multiclient deputies

    @super-admin @multiclient-multiple-clients-visible
    Scenario: Upload two CSV files where the second adds more clients to a deputy
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-1-single-row.csv"
        And I run the lay CSV command the file contains 1 new pre-registration entities

        # NB deputy UID in the next line has to correspond with the one in the uploaded CSVs
        And the Lay deputy user with deputy UID "97571940" and email "marbo.vantz@nowhere.1111.com" exists

        When I run the lay CSV command for 'lay-multiclient-1-single-row.csv'
        And "marbo.vantz@nowhere.1111.com" logs in
        Then I should see "Bert Vonk"

        # Upload same case plus two more clients for the same deputy
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-2-extra-rows.csv"
        And I run the lay CSV command the file contains 3 new pre-registration entities
        And I am on "/choose-a-client"
        Then I should see "Bert Vonk"
        And I should see "Able Werm"
        And I should see "Caspar Ghostfriendly"
