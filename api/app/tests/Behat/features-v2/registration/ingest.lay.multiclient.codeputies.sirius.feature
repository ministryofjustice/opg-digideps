@v2 @v2_sequential_1 @registration @ingest @multiclient
Feature: Lay CSV data ingestion - sirius source data for multiclient deputies

    @multiclient-codeputies-automatically-added-as-multiclients
    Scenario: Upload two CSV files where the second adds more clients to two co-deputies
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-codeputies-1.csv"
        And I run the lay CSV command for "lay-multiclient-codeputies-1.csv"
        And the lay deputy "Guuts Brineg" @ "ingest.lay.multiclient.codeputies.sirius.json" registers as a deputy
        When "guuts.brineg@nowhere.1111.com" logs in
        Then I should see "Virta Plool"

        Given a lay deputy "Ulu Frine" @ "ingest.lay.multiclient.codeputies.sirius.json" is invited to be a co-deputy for case "61513119"
        And I am on "/logout"
        And a lay deputy "Ulu Frine" @ "ingest.lay.multiclient.codeputies.sirius.json" completes their registration as a co-deputy for case "61513119"
        When "ulu.frine@nowhere.1111.com" logs in
        Then I should see "Virta Plool"
        And I am on "/logout"

        # Upload the original two court orders plus two new ones, one associated with each deputy
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-codeputies-2.csv"
        And I run the lay CSV command for "lay-multiclient-codeputies-2.csv"

        When "guuts.brineg@nowhere.1111.com" logs in
        And I am on "/choose-a-client"
        Then I should see "Virta Plool"
        And I should see "Mort Blump"
        And I should not see "Siren Blaster"
        And I am on "/logout"

        When "ulu.frine@nowhere.1111.com" logs in
        And I am on "/choose-a-client"
        Then I should see "Virta Plool"
        And I should see "Siren Blaster"
        And I should not see "Mort Blump"
        And I am on "/logout"
