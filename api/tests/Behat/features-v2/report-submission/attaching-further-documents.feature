@report-submissions @attaching-further-documents @v2 @v2_sequential_1
Feature: Attaching Further Documents

    @lay-pfa-high-submitted @super-admin
    Scenario: A user attempts to send further documents but there are no documents attached
        Given a Lay Deputy has submitted a report
        When I attached a supporting document "test-image.png" to the submitted report
        And I visit the send more documents page
        Then the send more documents page should not contain any documents to upload
        When I continue to submit the empty form
        Given a super admin user accesses the admin app
        Given the document sync enabled flag is set to '0'
        When I navigate to the admin report submissions page
        And I search for submissions using the court order number of the client I am interacting with and check the "New" column
        Then I should not see the submission under the "New" tab with the court order number of the user I am interacting with
        Given the document sync enabled flag is set to '1'

    @lay-pfa-high-submitted @super-admin
    Scenario: A user attempts to send further documents
        Given a Lay Deputy has submitted a report
        When I attached a supporting document "test-image.png" to the submitted report
        Given a super admin user accesses the admin app
        When I navigate to the admin report submissions page
        And I search for submissions using the court order number of the client I am interacting with and check the "Pending" column
        Then I should see the submission under the "Pending" tab with the court order number of the user I am interacting with
