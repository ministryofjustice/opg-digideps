@v2 @v2_sequential_1 @registration @ingest @multiclient
Feature: Lay CSV data ingestion - sirius source data for multiclient deputies

    @super-admin @multiclient-codeputies-multiple-clients-visible
    Scenario: Upload two CSV files where the second adds more clients to two co-deputies
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-codeputies-1.csv"
        And I run the lay CSV command for "lay-multiclient-codeputies-1.csv"
        And the lay deputy "Marbo Vantz" @ "ingest.lay.multiclient.codeputies.sirius.json" registers as a deputy
        When "marbo.vantz@nowhere.1111.com" logs in
        Then I should see "Bert Vonk"

        Given a lay deputy "Ulu Frine" @ "ingest.lay.multiclient.codeputies.sirius.json" is invited to be a co-deputy for case "91513119"
        And I am on "/logout"
        And a lay deputy "Ulu Frine" @ "ingest.lay.multiclient.codeputies.sirius.json" completes their registration as a co-deputy for case "91513119"
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

        # Able Verm is discharged and not present in this CSV file
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-codeputies-3.csv"
        And I run the lay CSV command for "lay-multiclient-codeputies-3.csv"

        # Doesn't work yet
        #When "marbo.vantz@nowhere.1111.com" logs in
        #And I am on "/choose-a-client"
        #Then I should see "Bert Vonk"
        #And I should not see "Able Werm"
        #And I should see "Caspar Ghostfriendly"
        #And print last response
        #And I am on "/logout"
