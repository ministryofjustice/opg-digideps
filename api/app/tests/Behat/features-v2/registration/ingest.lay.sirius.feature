@v2 @v2_sequential_1 @registration @ingest
Feature: Lay CSV data ingestion - sirius source data

    @super-admin
    Scenario: Uploading a Lay CSV that contains new pre-registration entities only
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload a lay CSV that contains 3 new pre-registration entities
        Then the new 'lay' entities should be added to the database
        And the count of the new 'lay' entities added should be displayed on the page

    @super-admin
    Scenario: Uploading a Lay CSV that contains existing pre-registration entities with new report type
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
    Scenario: Uploading a Lay CSV that contains contains a row with an invalid report type
        Given a super admin user accesses the admin app
        When I visit the admin upload lay users page
        And I upload a lay CSV that has 1 row with an invalid report type and 1 valid row
        Then I should see an alert showing the row was skipped on the 'lay' csv upload page
        And the new 'lay' entities should be added to the database
        And the count of the new 'lay' entities added should be displayed on the page

    @super-admin
    Scenario: Uploading a Lay CSV that has missing required columns
        Given a super admin user accesses the admin app
        When I visit the admin upload lay users page
        And I upload a 'lay' CSV that does not have any of the required columns
        Then I should see an error showing which columns are missing on the 'lay' csv upload page