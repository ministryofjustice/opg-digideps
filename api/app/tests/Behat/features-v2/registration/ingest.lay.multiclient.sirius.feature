@v2 @v2_sequential_1 @registration @ingest @multiclient
Feature: Lay CSV data ingestion - sirius source data for multiclient deputies

    @multiclient-multiple-clients-visible
    Scenario: Upload two CSV files where the second adds more clients to a deputy
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-1.csv"

        # NB deputy UIDs in the next line has to correspond with those in the uploaded CSVs
        And the Lay deputy user with deputy UID "17571940" and email "lekko.plip@nowhere.1111.com" exists
        And I run the lay CSV command for "lay-multiclient-1.csv"
        When "lekko.plip@nowhere.1111.com" logs in
        Then I should see "Bert Vonk"

        # Upload same case plus two more clients for the same deputy
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-2.csv"
        And I run the lay CSV command for "lay-multiclient-2.csv"

        When "lekko.plip@nowhere.1111.com" logs in
        And I am on "/choose-a-client"
        Then I should see "Bert Vonk"
        And I should see "Able Werm"
        And I should see "Caspar Ghostfriendly"
        And I am on "/logout"
