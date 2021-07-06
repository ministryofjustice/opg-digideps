@v2 @registration @ingest
Feature: Org CSV data ingestion - casrec source data

    @super-admin
    Scenario: Uploading a CSV that contains new clients and named deputies only
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload a 'casrec' org CSV that contains the following new entities:
            | clients | named_deputies | organisations | reports |
            | 3       | 2              | 2             | 3       |
        Then the new 'org' entities should be added to the database
        And the count of the new 'org' entities added should be displayed on the page

    @super-admin
    Scenario: Uploading a CSV that contains existing clients and named deputies - client made date and new named deputy in same firm
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload a 'casrec' org CSV that has a new made date '20-Aug-2019' and named deputy 'MAYOR MCCRACKEN' within the same org as the clients existing name deputy
        Then the clients made date and named deputy should be updated

    @super-admin
    Scenario: Uploading a CSV that contains existing clients and named deputies - named deputy address and phone updated
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload a 'casrec' org CSV that has a new address '75 Plutonium Way, Salem, Witchington, Barberaham, Townsville, TW5 V78' for an existing named deputy
        Then the named deputy's address should be updated

    @super-admin
    Scenario: Uploading a CSV that contains existing clients and named deputies - report type updated
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload a 'casrec' org CSV that has a new report type '103-5' for an existing report that has not been submitted or unsubmitted
        Then the report type should be updated

    @super-admin
    Scenario: Uploading a CSV that contains deputies with missing required information alongside valid deputy rows
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload a 'casrec' org CSV that has 1 row with missing values 'Last Report Day, Made Date, Email' for case number '70000000' and 1 valid row
        Then I should see an error showing the problem on the 'org' csv upload page
        And the new 'org' entities should be added to the database
        And the count of the new 'org' entities added should be displayed on the page

    @super-admin
    Scenario: Uploading a CSV that has missing required columns
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload a 'casrec' 'org' CSV that does not have any of the required columns
        Then I should see an error showing which columns are missing on the 'org' csv upload page

    @super-admin
    Scenario: Uploading a CSV that has an unexpected column
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload a 'casrec' org CSV that has an 'NDR' column
        Then I should see an error showing the column that was unexpected
