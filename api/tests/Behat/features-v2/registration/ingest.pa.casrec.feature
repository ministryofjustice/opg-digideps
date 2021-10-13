@v2 @registration @ingest @v2_admin
Feature: Org CSV data ingestion - casrec source data (PA)

    @super-admin
    Scenario: Uploading a PA CSV with a missing 'DepAddr No' column that contains new clients and named deputies only
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload a 'casrec' 'PA' CSV that contains the following new entities:
            | clients | named_deputies | organisations | reports |
            | 3       | 2              | 1             | 3       |
        Then the new 'PA' entities should be added to the database
        And the count of the new 'PA' entities added should be displayed on the page
