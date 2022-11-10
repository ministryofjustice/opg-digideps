@v2 @registration @ingest @v2_sequential
Feature: Org CSV data ingestion - sirius source data

    @super-admin
    Scenario: Uploading a CSV that contains new clients and named deputies only
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload an org CSV that contains the following new entities:
            | clients | named_deputies | organisations | reports |
            | 3       | 2              | 2             | 3       |
        Then the new 'org' entities should be added to the database
        And the count of the new 'org' entities added should be displayed on the page

    @super-admin
    Scenario: Uploading a CSV that contains existing clients and named deputies - new named deputy in same firm
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload an org CSV that has a new named deputy 'MAYOR MCCRACKEN' within the same org as the clients existing name deputy
        Then the clients named deputy should be updated

#    @super-admin
#    Scenario: Uploading a CSV that contains existing clients and named deputies - named deputy address and phone updated
#        Given a super admin user accesses the admin app
#        When I visit the admin upload org users page
#        And I upload an org CSV that has a new address '75 Plutonium Way, Salem, Witchington, Barberaham, Townsville, TW5 V78' for an existing named deputy
#        Then the named deputy's address should be updated

    @super-admin
    Scenario: Uploading a CSV that contains existing clients and named deputies - report type updated
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload an org CSV that has a new report type '103-5' for an existing report that has not been submitted or unsubmitted
        Then the report type should be updated

#    @super-admin
#    Scenario: Uploading a CSV that contains a new named deputy in a new organisation for an existing client - same case number, same made date
#        Given a super admin user accesses the admin app
#        When I visit the admin upload org users page
#        And I upload an org CSV that has a new named deputy in a new organisation for an existing client
#        Then the named deputy associated with the client should be updated to the new named deputy
#        And the organisation associated with the client should be updated to the new organisation
#        And the report associated with the client should remain the same

    #Temporary test to ensure that we are not updating the client if the organisation has changed
    @super-admin
    Scenario: Uploading a CSV that contains a new named deputy in a new organisation for an existing client - same case number, same made date
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload an org CSV that has a new named deputy in a new organisation for an existing client
        Then the named deputy associated with the client should remain the same
        And the organisation associated with the client should remain the same
        And the report associated with the client should remain the same

    @super-admin
    Scenario: Uploading a CSV where an existing client's named deputy has changed firm  - same case number, same made date
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload an org CSV that contains a new org email and street address but the same deputy number for an existing clients named deputy
        Then the organisation associated with the client should be updated to the new organisation
        And the named deputy's address should be updated to '88 BROAD WALK, ALINGHAM, CORK, VALE, TOWNSVILLE, TW8 R55'
        And the named deputy associated with the client should remain the same
        And the report associated with the client should remain the same

#    @super-admin
#    Scenario: Uploading a CSV that contains a new named deputy in a new organisation for an existing client - same case number, new made date
#        Given a super admin user accesses the admin app
#        When I visit the admin upload org users page
#        And I upload an org CSV that has a an existing case number and new made date for an existing client
#        Then a new report should be generated for the client

    @super-admin
    Scenario: Uploading a CSV where the same named deputy appears with two addresses
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload an org CSV that contains two rows with the same named deputy at two different addresses with different deputy uids
        Then there should be two named deputies created
        And the named deputy for case number '97864531' should have the address '6 MAYFIELD AVENUE, WYLDE GREEN, SUTTON COLDFIELD, WEST MIDLANDS, WARWICKSHIRE, B73 5QQ'
        And the named deputy for case number '64597832' should have the address '21 NIGEL ROAD, NORTHFIELD, BIRMINGHAM, WEST MIDLANDS, WARWICKSHIRE, B31 1LL'

    @super-admin
    Scenario: Uploading a CSV that contains deputies with missing required information alongside valid deputy rows
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload an org CSV that has 1 row with missing values 'LastReportDay, MadeDate, DeputyEmail' for case number '70000000' and 1 valid row
        Then I should see an error showing the problem on the 'org' csv upload page
        And the new 'org' entities should be added to the database
        And the count of the new 'org' entities added should be displayed on the page

    @super-admin
    Scenario: Uploading a CSV that has missing required columns
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload an 'org' CSV that does not have any of the required columns
        Then I should see an error showing which columns are missing on the 'org' csv upload page

    @super-admin
    Scenario: Uploading a CSV that has an unexpected column
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload an org CSV that has an 'NDR' column
        Then I should see an error showing the column that was unexpected

    @super-admin
    Scenario: Uploading a CSV that has an organisation name but missing deputy first and last name
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload an org CSV that has an organisation name 'Conglom-O Corporation' but missing deputy first and last name
        Then the named deputy 'first' name should be 'Conglom-O Corporation'
        And the named deputy 'last' name should be 'empty'

    @super-admin
    Scenario: Uploading a CSV that contains deputy name updates for existing deputies
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload an org CSV that has one person deputy and one organisation deputy
        Then the new 'org' entities should be added to the database
        When I visit the admin upload org users page
        And I upload an org CSV that updates the person deputy with an org name and the org deputy with a person name
        Then the named deputy with deputy UID '19921992' should have the full name 'HYPERPOP Inc.'
        And the named deputy with deputy UID '19901990' should have the full name 'Alexander Cook'

    @super-admin
    Scenario: Uploading a CSV that contains deputy email updates for existing deputies
        Given a super admin user accesses the admin app
        When I visit the admin upload org users page
        And I upload an org CSV that has one person deputy and one organisation deputy
        Then the new 'org' entities should be added to the database
        When I visit the admin upload org users page
        And I upload an org CSV that updates the deputy's email
        Then the named deputy with deputy UID '19921992' should have the email 'example@example.com'
