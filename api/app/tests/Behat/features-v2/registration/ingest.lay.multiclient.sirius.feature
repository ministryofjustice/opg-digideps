@v2 @v2_sequential_1 @registration @ingest @multiclient
Feature: Lay CSV data ingestion - sirius source data for multiclient deputy

    @multiclient-codeputies-multiple-clients-visible
    Scenario: Upload two CSV files where the second adds more clients to a deputy
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-1.csv"
        And I run the lay CSV command for "lay-multiclient-1.csv"
        And the lay deputy "Marbo Vantz" @ "ingest.lay.multiclient.sirius.json" registers as a deputy
        When "marbo.vantz@nowhere.1111.com" logs in
        Then I should see "Bert Vonk"

        # Upload the original case plus two new ones, each associated with both deputies
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-2.csv"
        And I run the lay CSV command for "lay-multiclient-2.csv"

        When "marbo.vantz@nowhere.1111.com" logs in
        And I am on "/choose-a-client"
        Then I should see "Bert Vonk"
        And I should see "Able Werm"
        And I should see "Caspar Ghostfriendly"
        And I am on "/logout"
