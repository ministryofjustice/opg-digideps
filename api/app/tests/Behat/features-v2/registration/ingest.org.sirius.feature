@v2 @v2_sequential_3 @registration @ingest
Feature: Org CSV data ingestion - sirius source data

    @super-admin
    Scenario: Uploading a CSV that contains new clients and named deputies only
        Given a csv has been uploaded to the sirius bucket with the file 'org-3-valid-rows.csv'
        When I run the lay CSV command the file contains the following new entities:
            | clients | deputies | organisations | reports |
            | 3       | 2              | 2             | 3       |
        Then the new 'org' entities should be added to the database
        And the count of the new 'org' entities added should be in the command output

    @super-admin
    Scenario: Uploading a CSV that contains existing clients and named deputies - new named deputy in same firm
        Given a csv has been uploaded to the sirius bucket with the file 'org-1-updated-row-new-named-deputy.csv'
        When I run the lay CSV command the file has a new named deputy 'MAYOR MCCRACKEN' within the same org as the clients existing name deputy
        Then the clients named deputy should be updated
        And the count of the new 'org' entities added should be in the command output

    @super-admin
    Scenario: Uploading a CSV that contains existing clients and named deputies - named deputy address and phone updated
        Given a csv has been uploaded to the sirius bucket with the file 'org-1-updated-row-named-deputy-address.csv'
        When I run the lay CSV command the file has a new address '75 Plutonium Way, Salem, Witchington, Barberaham, Townsville, TW5 V78' for an existing named deputy
        Then the named deputy's address should be updated
        And the count of the new 'org' entities added should be in the command output

    @super-admin
    Scenario: Uploading a CSV that contains existing clients and named deputies - report type updated
        Given a csv has been uploaded to the sirius bucket with the file 'org-1-updated-row-report-type.csv'
        Then I run the lay CSV command the file has a new report type '103-5' for an existing report that has not been submitted or unsubmitted
        And the report type should be updated
        And the count of the new 'org' entities added should be in the command output

    @super-admin
    Scenario: Uploading a CSV that contains an existing dual case - report type updated only when deputy uid matches
        Given a csv has been uploaded to the sirius bucket with the file 'org-2-rows-1-row-updated-report-type-dual-case.csv'
        When I run the lay CSV command the file has a new report type '103-5' for a dual case
        Then the report type should be updated
        And the count of the new 'org' entities added should be in the command output

#    @super-admin
#    Scenario: Uploading a CSV that contains a new named deputy in a new organisation for an existing client - same case number, same made date
#        Given a super admin user accesses the admin app
#        When I upload an org CSV that has a new named deputy in a new organisation for an existing client
#        Then the named deputy associated with the client should be updated to the new named deputy
#        And the organisation associated with the client should be updated to the new organisation
#        And the report associated with the client should remain the same

    #Temporary test to ensure that we are not updating the client if the organisation has changed
    @super-admin
    Scenario: Uploading a CSV that contains a new named deputy in a new organisation for an existing client - same case number, same made date
        Given a csv has been uploaded to the sirius bucket with the file 'org-1-row-new-named-deputy-and-org-existing-client.csv'
        When I run the lay CSV command the file has a new named deputy in a new organisation for an existing client
        Then the named deputy associated with the client should remain the same
        And the organisation associated with the client should remain the same
        And the report associated with the client should remain the same

    @super-admin
    Scenario: Uploading a CSV where an existing client's named deputy has changed firm  - same case number, same made date
        Given a csv has been uploaded to the sirius bucket with the file 'org-1-row-existing-named-deputy-and-client-new-org-and-street-address.csv'
        When I run the lay CSV command the file contains a new org email and street address but the same deputy number for an existing clients named deputy
        Then the organisation associated with the client should be updated to the new organisation
        And the named deputy's address should be updated to '88 BROAD WALK, ALINGHAM, CORK, VALE, TOWNSVILLE, TW8 R55'
        And the named deputy associated with the client should remain the same
        And the report associated with the client should remain the same
        And the count of the new 'org' entities added should be in the command output

#    @super-admin
#    Scenario: Uploading a CSV that contains a new named deputy in a new organisation for an existing client - same case number, new made date
#        Given a super admin user accesses the admin app
#        When I upload an org CSV that has a an existing case number and new made date for an existing client
#        Then a new report should be generated for the client

    @super-admin
    Scenario: Uploading a CSV where the same named deputy appears with two addresses
        Given a csv has been uploaded to the sirius bucket with the file 'org-2-rows-1-named-deputy-with-different-addresses.csv'
        When I run the lay CSV command the file contains two rows with the same named deputy at two different addresses with different deputy uids
        Then there should be two named deputies created
        And the named deputy for case number '97864531' should have the address '6 MAYFIELD AVENUE, WYLDE GREEN, SUTTON COLDFIELD, WEST MIDLANDS, WARWICKSHIRE, B73 5QQ'
        And the named deputy for case number '64597832' should have the address '21 NIGEL ROAD, NORTHFIELD, BIRMINGHAM, WEST MIDLANDS, WARWICKSHIRE, B31 1LL'
        And the count of the new 'org' entities added should be in the command output

    @super-admin
    Scenario: Uploading a CSV that contains deputies with missing required information alongside valid deputy rows
        Given a csv has been uploaded to the sirius bucket with the file 'org-1-row-missing-last-report-date-1-valid-row.csv'
        When I run the lay CSV command the file has 1 row with missing values 'LastReportDay, MadeDate, DeputyEmail' for case number '70000000' and 1 valid row
        Then the new 'org' entities should be added to the database
        And the count of the new 'org' entities added should be in the command output

# Needs further rewrite so we're gracefully handling missing columns & not just stopping the process.
# Currently throws critical error
#    @super-admin
#    Scenario: Uploading a CSV that has missing required columns
#        Given a super admin user accesses the admin app
#        When I upload an 'org' CSV that does not have any of the required columns
#        Then I should see an error showing which columns are missing on the 'org' csv upload page
#        And the count of the new 'org' entities added should be in the command output
# As above
#    @super-admin
#    Scenario: Uploading a CSV that has an unexpected column
#        Given a super admin user accesses the admin app
#        When I upload an org CSV that has an 'NDR' column
#        Then I should see an error showing the column that was unexpected
#        And the count of the new 'org' entities added should be in the command output

    @super-admin
    Scenario: Uploading a CSV that has an organisation name but missing deputy first and last name
        Given a csv has been uploaded to the sirius bucket with the file 'org-1-row-1-named-deputy-with-org-name-no-first-last-name.csv'
        When I run the lay CSV command the file has an organisation name 'Conglom-O Corporation' but missing deputy first and last name
        Then the named deputy 'first' name should be 'Conglom-O Corporation'
        And the named deputy 'last' name should be 'empty'
        And the count of the new 'org' entities added should be in the command output

    @super-admin
    Scenario: Uploading a CSV that contains deputy name updates for existing deputies
        Given a csv has been uploaded to the sirius bucket with the file 'org-2-rows-1-person-deputy-1-org-deputy.csv'
        When I run the lay CSV command the file has one person deputy and one organisation deputy
        Then the new 'org' entities should be added to the database
        And the count of the new 'org' entities added should be in the command output
        Given a csv has been uploaded to the sirius bucket with the file 'org-2-rows-1-person-deputy-1-org-deputy-updated-names.csv'
        When I run the lay CSV command the file that updates the person deputy with an org name and the org deputy with a person name
        Then the named deputy with deputy UID '19921992' should have the full name 'HYPERPOP Inc.'
        And the named deputy with deputy UID '19901990' should have the full name 'Alexander Cook'
        And the count of the new 'org' entities added should be in the command output

    @super-admin
    Scenario: Uploading a CSV that contains deputy email updates for existing deputies
        Given a csv has been uploaded to the sirius bucket with the file 'org-2-rows-1-person-deputy-1-org-deputy-2ndRun.csv'
        When I run the lay CSV command the file has one person deputy and one organisation deputy 2nd run
        Then the new 'org' entities should be added to the database
        And the count of the new 'org' entities added should be in the command output
        Given a csv has been uploaded to the sirius bucket with the file 'org-2-rows-1-person-deputy-1-org-deputy-updated-emails.csv'
        When I run the lay CSV command the file that updates the deputy's email
        Then the named deputy with deputy UID '19921993' should have the email 'example@example-email.com'
        And the count of the new 'org' entities added should be in the command output

    @admin-manager
    Scenario: An admin manager is unable to upload an Org CSV when logged into the admin app
        Given an admin manager user accesses the admin app
        When I visit the admin upload users page
        And I attempt to upload a 'org' CSV
        Then I should be redirected and denied access to continue
