@v2 @v2_sequential_1 @registration @ingest @multiclient
Feature: Lay CSV data ingestion - sirius source data for multiclient deputies

    @super-admin @multiclient-multiple-clients-one-discharged
    Scenario: Upload a CSV file with two clients, then discharge one
        # see https://opgtransform.atlassian.net/browse/DDLS-457, scenario 4
        # NB even though this CSV has two rows, only the row 1 (whose details the deputy uses to register with) will be
        # associated with them; on the second pass through the CSV, at which point we have the deputy UID
        # associated with the user, row 2 will be automatically associated with them as well
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-discharged.csv"
        And I run the lay CSV command for "lay-multiclient-discharged.csv"
        And the lay deputy "Frankie Velocity" @ "ingest.lay.multiclient.discharged.sirius.json" registers as a deputy
        When "frankie.velocity@nowhere.1111.com" logs in
        Then I should see "Mibbleblip Taralavalantula"
        And I should not see "Vernon Scop"
        And I am on "/logout"

        # On second pass through the CSV, the second client is associated with the now registered deputy
        Given a csv has been uploaded to the sirius bucket with the file "lay-multiclient-discharged.csv"
        And I run the lay CSV command for "lay-multiclient-discharged.csv"
        When "frankie.velocity@nowhere.1111.com" logs in
        Then I should see "Mibbleblip Taralavalantula"
        And I should see "Vernon Scop"
        And I am on "/logout"

        # The client the deputy has been discharged from should no longer be shown
        Given a super admin discharges the deputy from "98788669"
        And I am on admin page "/logout"
        When "frankie.velocity@nowhere.1111.com" logs in
        Then I should not see "Mibbleblip Taralavalantula"
        And I should see "Vernon Scop"
        And I am on "/logout"
