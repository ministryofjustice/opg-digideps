@v2 @csv-data-ingestion
Feature: Org CSV data ingestion - casrec source data

    @super-admin
    Scenario: Uploading a CSV that contains new clients and named deputies only
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload a 'casrec' CSV that contains the following new entities:
            | clients | named_deputies | organisations | reports |
            | 3       | 2              | 2             | 3       |
        Then the new entities should be added to the database

    @super-admin
    Scenario: Uploading a CSV that contains existing clients and named deputies - client made date and new named deputy in same firm
        Given a super admin user accesses the admin app
        When I visit the upload users page
        And I upload a 'casrec' CSV that has a new made date and named deputy within the same org as the clients existing name deputy
        Then the clients made date and named deputy should be updated

    Scenario: Uploading a CSV that contains existing clients and named deputies - named deputy address and phone updated
        Given a super admin user accesses the admin app
        When I visit the upload users page
        And I upload a 'casrec' CSV that has a new address and phone details for an existing named deputy
        Then the named deputies address and phone number should be updated

    Scenario: Uploading a CSV that contains existing clients and named deputies - report type updated
        Given a super admin user accesses the admin app
        When I visit the upload users page
        And I upload a 'casrec' CSV that has a new report type for an existing report that has not been submitted or unsubmitted
        Then the type of the report should be updated

    Scenario: Uploading a CSV that contains deputies with missing required information
        Given a super admin user accesses the admin app
        When I visit the upload users page
        And I upload a 'casrec' CSV that has missing values for 'Report Start Date' and 1 valid row
        Then I should see an error advising of the problem
        And the new entitiy should be added to the database

    Scenario: Uploading a CSV that has missing required columns
        Given a super admin user accesses the admin app
        When I visit the upload users page
        And I upload a 'casrec' CSV that does not have a column with value 'Deputy No'
        Then I should see an error advising of the problem

    Scenario: Uploading a CSV that has an unexpected column
        Given a super admin user accesses the admin app
        When I visit the upload users page
        And I upload a 'casrec' CSV that has a column 'NDR'
        Then I should see an error advising of the problem
