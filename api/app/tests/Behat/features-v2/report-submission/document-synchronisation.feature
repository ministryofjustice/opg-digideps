@v2 @v2_sequential_2 @report-submissions @document-sync
Feature: Synchronising Documents with Sirius

    @super-admin @lay-pfa-high-completed
    Scenario: Submitting a report sets the synchronisation status to queued
        Given a Lay Deputy has a completed report
        When I view the documents report section
        And I have documents to upload
        And I attach a supporting document "test-image.png" to the report
        Then I follow the submission process to the declaration page for current report
        And I fill in the declaration page and submit the report
        Then my report should be submitted
        And a super admin user accesses the admin app
        And I visit the admin submissions page
        And I search for submissions using the court order number of the client I am interacting with and check the 'Pending' column
        Then I should see the case number of the user I'm interacting with
        And the report PDF document should be queued
        And the document "test_image.png" should be queued

    @super-admin @prof-admin-health-welfare-submitted
    Scenario: Submitting supporting documents after a report submission sets the synchronisation status to queued
        Given a Professional Deputy has submitted a Health and Welfare report
        And I attach a supporting document "testimage2.png" to the submitted report
        And I send the documents to complete the upload process on the "submitted" report
        And a super admin user accesses the admin app
        And I visit the admin submissions page
        And I search for submissions using the court order number of the client I am interacting with and check the 'Pending' column
        Then I should see the case number of the user I'm interacting with
        And the document "testimage2.png" should be queued

    @super-admin @prof-admin-health-welfare-submitted
    Scenario: Running the document-sync command syncs queued documents with Sirius
        Given a Professional Deputy has submitted a Health and Welfare report
        And I attach a supporting document "testimage3.png" to the submitted report
        And I send the documents to complete the upload process on the "submitted" report
        When a super admin user accesses the admin app
        And the document sync enabled flag is set to '1'
        And I run the document-sync command
        And I visit the admin submissions page
        And I search for submissions using the court order number of the client I am interacting with and check the 'Pending' column
        Then I should see the case number of the user I'm interacting with
        And the report PDF document should be synced
        And the document "testimage3.png" should be queued
        And I run the document-sync command
        And I visit the admin submissions page
        And I search for submissions using the court order number of the client I am interacting with and check the 'Synchronised' column
        And the document "testimage3.png" should be synced
