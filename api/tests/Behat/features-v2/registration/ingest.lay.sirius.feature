@v2 @registration @ingest @v2_admin @acs
Feature: Lay CSV data ingestion - sirius source data

    @super-admin
    Scenario: Uploading a Lay CSV that contains new casrec entities only
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload a lay CSV that contains 3 new casrec entities
        Then the new 'lay' entities should be added to the database
        And the count of the new 'lay' entities added should be displayed on the page

    @super-admin
    Scenario: Uploading a Lay CSV that contains existing casrec entities with new report type
        Given a super admin user accesses the admin app
        When I visit the admin upload lay users page
        And I upload a lay CSV that has a new report type '103' for case number '34343434'
        Then the clients report type should be updated

    @super-admin
    Scenario: Uploading a Lay CSV that contains deputies with missing required information alongside valid deputy rows
        Given a super admin user accesses the admin app
        When I visit the admin upload lay users page
        And I upload a lay CSV that has 1 row with missing values for 'caseNumber, clientLastname, deputyUid and deputySurname' and 1 valid row
        Then I should see an error showing the problem on the 'lay' csv upload page
        And the new 'lay' entities should be added to the database
        And the count of the new 'lay' entities added should be displayed on the page

    @super-admin
    Scenario: Uploading a Lay CSV that has missing required columns
        Given a super admin user accesses the admin app
        When I visit the admin upload lay users page
        And I upload a 'sirius' 'lay' CSV that does not have any of the required columns
        Then I should see an error showing which columns are missing on the 'lay' csv upload page
