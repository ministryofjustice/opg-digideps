@v2 @v2_sequential_2 @report-submissions @document-sync @document-synchronisation
Feature: Synchronising Documents with Sirius

    @super-admin @prof-admin-health-welfare-submitted
    Scenario: Submitting supporting documents after a report submission sets the synchronisation status to queued
        Given a Professional Deputy has submitted a Health and Welfare report
        And I attach a supporting document "testimage2.png" to the submitted report
        And I send the documents to complete the upload process on the submitted report
        And a super admin user accesses the admin app
        And I visit the admin submissions page
        And I search for submissions using the case number of the deputy I am interacting with and check the 'Pending' column
        Then I should see the case number of the user I'm interacting with
        And the document "testimage2.png" should be queued

    @super-admin @prof-admin-health-welfare-submitted
    Scenario: Running the document-sync command syncs queued documents with Sirius
        Given a Professional Deputy has submitted a Health and Welfare report
        And I attach a supporting document "testimage3.png" to the submitted report
        And I send the documents to complete the upload process on the submitted report
        When a super admin user accesses the admin app
        And the document sync enabled flag is set to '1'
        And I run the document-sync command
        And I visit the admin submissions page
        And I search for submissions using the case number of the deputy I am interacting with and check the 'Pending' column
        Then I should see the case number of the user I'm interacting with
        And the report PDF document should be synced
        And the document "testimage3.png" should be queued
        And I run the document-sync command
        And I visit the admin submissions page
        And I search for submissions using the case number of the deputy I am interacting with and check the 'Synchronised' column
        And the document "testimage3.png" should be synced
