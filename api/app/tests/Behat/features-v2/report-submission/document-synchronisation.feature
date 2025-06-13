@v2 @v2_sequential_2 @report-submissions @document-sync
Feature: Synchronising Documents with Sirius

    @super-admin @lay-pfa-high-completed
    Scenario: Submitting a report sets the synchronisation status to queued
        Given a Lay Deputy has a completed report
        When I view the documents report section
        And I have documents to upload
        And I attach a supporting document "test-image.png" to the report
        And I submit the report
        And a super admin user accesses the admin app
        And I visit the admin submissions page
        And I search for submissions using the court order number of the client I am interacting with and check the 'Pending' column
        Then I should see the case number of the user I'm interacting with
        And the report PDF document should be queued
        And the document "testimage.png" should be queued

    @super-admin @ndr-completed
    Scenario: Submitting an NDR sets the synchronisation status of the report PDF to queued
        Given a Lay Deputy has a completed NDR report
        And I submit the report
        And a super admin user accesses the admin app
        And I visit the admin submissions page
        And I search for submissions using the court order number of the client I am interacting with and check the 'Pending' column
        Then I should see the case number of the user I'm interacting with
        And the report PDF document should be queued

    @super-admin @prof-admin-health-welfare-submitted
    Scenario: Submitting supporting documents after a report submission sets the synchronisation status to queued
        Given a Professional Deputy has submitted a Health and Welfare report
        And I attach a supporting document "test-image.png" to the "submitted" report
        And a super admin user accesses the admin app
        And I visit the admin submissions page
        And I search for submissions using the court order number of the client I am interacting with and check the 'Pending' column
        Then I should see the case number of the user I'm interacting with
        And the document "testimage.png" should be queued

    @super-admin @prof-admin-health-welfare-submitted
    Scenario: Running the document-sync command syncs queued documents with Sirius
        Given a Professional Deputy has submitted a Health and Welfare report
        And I attach a supporting document "test-image.png" to the "submitted" report
        When a super admin user accesses the admin app
        And I run the document-sync command
        And I visit the admin submissions page
        And I search for submissions using the court order number of the client I am interacting with and check the 'Pending' column
        Then I should see the case number of the user I'm interacting with
        And the report PDF document should be synced
#       Supporting documents can only sync after report PDF has synced
        And the document "testimage.png" should be queued
        And I run the document-sync command
        And I visit the admin submissions page
        And I search for submissions using the court order number of the client I am interacting with and check the 'Synchronised' column
        And the document "testimage.png" should be synced

#    Need to convert supported documents at submission time rather than sync time due to OOM errors. Keeping
#    test steps here to be used on a future ticket.

#    @super-admin @lay-pfa-high-completed
#    Scenario: Documents in formats that can be converted are converted before being synced with Sirius
#        Given a Lay Deputy has a completed report
#        And the user uploaded a document with a file type that can be converted before the document conversion feature was released
#        When a super admin user accesses the admin app
#        And I run the document-sync command
#        And I visit the admin submissions page
#        And I search for submissions using the court order number of the client I am interacting with and check the 'Pending' column
#        Then I should see the case number of the user I'm interacting with
#        And the report PDF document should be synced
##       Supporting documents can only sync after report PDF has synced
#        And the document "good-heic.heic" should be queued
#        And I run the document-sync command
#        And I visit the admin submissions page
#        And I search for submissions using the court order number of the client I am interacting with and check the 'Synchronised' column
#        And the document "goodheic.jpeg" should be synced
