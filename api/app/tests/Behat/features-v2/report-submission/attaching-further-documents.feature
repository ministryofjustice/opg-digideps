@v2 @v2_sequential_3 @report-submissions @attaching-further-documents
Feature: Attaching Further Documents

    @lay-pfa-high-submitted @super-admin
    Scenario: A user attempts to send further documents
        Given a Lay Deputy has submitted a report
        When I attach a supporting document "test-image.png" to the submitted report
        And I send the documents to complete the upload process on the "submitted" report
        Then I should be on the Lay homepage
        And a flash message should be displayed to the user confirming the document upload
        Given a super admin user accesses the admin app
        When I navigate to the admin report submissions page
        And I search for submissions using the court order number of the client I am interacting with and check the "Pending" column
        Then I should see the submission under the "Pending" tab with the court order number of the user I am interacting with

    @lay-pfa-high-submitted @super-admin
    Scenario: A user attempts to send further documents in two attempts
        Given a Lay Deputy has submitted a report
        When I attach a supporting document "test-image.png" to the submitted report
        And I send the documents to complete the upload process on the "submitted" report
        Then I should be on the Lay homepage
        And a flash message should be displayed to the user confirming the document upload
        When I visit the documents step 2 page
        Then I should see "testimage.png" listed as a previously submitted document
        When I attach a "second" supporting document "good.pdf" to the submitted report
        And I send the documents to complete the upload process on the "submitted" report
        Then I should be on the Lay homepage
        When I visit the documents step 2 page
        Then I should see "testimage.png" and "good.pdf" as previously submitted documents

    @lay-pfa-high-submitted @super-admin
    Scenario: A user attempts to remove a file after it has been selected
        Given a Lay Deputy has submitted a report
        When I attach a supporting document "test-image.png" to the submitted report
        And I remove the "testimage.png" document I uploaded
        Then a flash message should be displayed to the user confirming the removal of "testimage.png"
        And the document upload page should not contain any documents
