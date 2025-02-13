@v2 @v2_sequential_1 @registration @ingest @dual
Feature: Lay CSV data ingestion - sirius source data for dual deputies

    @dual
    Scenario: If the CSV associates two deputies with a case, but one for the pfa only and the other for the hw only, they see different reports
        # The assumption is that the two deputies in the CSV have already registered
        # for other cases; but then the CSV associates them with a new case in dual mode, where one is the deputy
        # for the hw court order on that case, and the other the deputy for the pfa court order on it.
        # This scenario is unlikely at the moment, but may become more likely if deputies are allowed to
        # register for an existing case.
        Given a csv has been uploaded to the sirius bucket with the file "lay-dual.csv"
        And the Lay deputy user with deputy UID "37476940", email "sklobo.meb@nowhere.1111.com", first name "Sklobo", and last name "Meb" exists
        And the Lay deputy user with deputy UID "37476941", email "pips.magento@nowhere.1111.com", first name "Pips", and last name "Magento" exists
        And I run the lay CSV command for "lay-dual.csv"

        When "sklobo.meb@nowhere.1111.com" logs in
        And I visit the report overview page
        # report should contain pfa-specific sections but not hw-specific sections
        Then I should see "Adelard's property and finances"
        And I should see "client-benefits-check" as "not started"
        And I should see "bank-accounts" as "not started"
        And I should see "deputy-expenses" as "not started"
        And I should see "gifts" as "not started"
        And I should see "money-transfers" as "no transfers"
        And I should see "money-in" as "not started"
        And I should see "money-out" as "not started"
        And I should see "balance" as "more information needed"
        And I should see "assets" as "not started"
        And I should see "debts" as "not started"
        And I should not see "lifestyle"
        And I am on "/logout"

        When "pips.magento@nowhere.1111.com" logs in
        And I visit the report overview page
        # report should contain hw-specific sections but not pfa-specific sections
        Then I should see "lifestyle" as "not started"
        And I should not see "Adelard's property and finances"
        And I should not see "client-benefits-check" report section
        And I should not see "bank-accounts" report section
        And I should not see "deputy-expenses" report section
        And I should not see "gifts" report section
        And I should not see "money-transfers" report section
        And I should not see "money-in" report section
        And I should not see "money-out" report section
        And I should not see "balance" report section
        And I should not see "assets" report section
        And I should not see "debts" report section
        And I am on "/logout"
