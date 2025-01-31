@v2 @v2_sequential_1 @registration @ingest @multiclient
Feature: Lay CSV data ingestion - sirius source data for multiclient deputies

    @super-admin @multiclient-codeputies-multiple-clients-visible
    Scenario: Upload two CSV files where the second adds more clients to two co-deputies
        # NB deputy UIDs in the next line has to correspond with those in the uploaded CSVs
        Given the Lay deputy user with deputy UID "97571940" and email "marbo.vantz@nowhere.1111.com" exists
        And the Lay deputy user with deputy UID "97571941" and email "ulu.frine@nowhere.1111.com" exists

        And a csv has been uploaded to the sirius bucket with the file "lay-multiclient-codeputies-1.csv"
        And I run the lay CSV command for "lay-multiclient-codeputies-1.csv"

        When "marbo.vantz@nowhere.1111.com" logs in
        Then I should see "Bert Vonk"
        And I am on "/logout"

        When "ulu.frine@nowhere.1111.com" logs in
        Then I should see "Bert Vonk"
        And I am on "/logout"

        # Upload same case plus two more clients for the same deputy
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-codeputies-2.csv"
        And I run the lay CSV command for "lay-multiclient-codeputies-2.csv"

        When "marbo.vantz@nowhere.1111.com" logs in
        And I am on "/choose-a-client"
        Then I should see "Bert Vonk"
        And I should see "Able Werm"
        And I should see "Caspar Ghostfriendly"
        And I am on "/logout"

        When "ulu.frine@nowhere.1111.com" logs in
        And I am on "/choose-a-client"
        Then I should see "Bert Vonk"
        And I should see "Able Werm"
        And I should see "Caspar Ghostfriendly"
        And I am on "/logout"
