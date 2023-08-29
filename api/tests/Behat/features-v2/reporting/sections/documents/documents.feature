@v2 @v2_sequential_3 @documents
Feature: Documents - All User Roles

    @lay-pfa-high-not-started
    Scenario: A user has no supporting documents to add
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have no documents to upload
        Then I should be on the documents summary page
        And the documents summary page should not contain any documents
        When I follow link back to report overview page
        Then I should see "documents" as "no documents"

    @lay-pfa-high-not-started
    Scenario: A user uploads one supporting document that has a valid file type
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload one valid document
        Then the documents uploads page should contain the documents I uploaded
        When I have no further documents to upload
        Then I should be on the documents summary page
        And the documents summary page should contain the documents I uploaded
        When I follow link back to report overview page
        Then I should see "documents" as "1 document"

    @lay-pfa-high-not-started
    Scenario: A user uploads multiple supporting documents that have valid file types and do not require conversion
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload multiple valid documents that do not require conversion
        Then the documents uploads page should contain the documents I uploaded
        When I have no further documents to upload
        Then I should be on the documents summary page
        And the documents summary page should contain the documents I uploaded
        When I follow link back to report overview page
        Then I should see "documents" as "3 documents"

    @lay-pfa-high-not-started
    Scenario: A user uploads multiple supporting documents that have valid file types and require conversion
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload multiple valid documents that require conversion
        Then the documents uploads page should contain the documents I uploaded with converted filenames
        When I have no further documents to upload
        Then I should be on the documents summary page
        And the documents summary page should contain the documents I uploaded with converted filenames
        When I follow link back to report overview page
        Then I should see "documents" as "2 documents"

    @lay-pfa-high-not-started
    Scenario: A user deletes one supporting document they uploaded from the uploads page
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload multiple valid documents that do not require conversion
        And I remove one document I uploaded
        When I have no further documents to upload
        Then I should be on the documents summary page
        And the documents summary page should contain the documents I uploaded
        When I follow link back to report overview page
        Then I should see "documents" as "2 documents"

    @lay-pfa-high-not-started
    Scenario: A user deletes one supporting document they uploaded from the summary page
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload multiple valid documents that do not require conversion
        When I have no further documents to upload
        Then I should be on the documents summary page
        When I remove one document I uploaded
        Then the documents summary page should contain the documents I uploaded

    @lay-pfa-high-not-started
    Scenario: A user uploads one supporting document that has an invalid file type
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload one document with an unsupported file type
        Then I should see an 'invalid file type' error

    @lay-pfa-high-not-started
    Scenario: A user uploads one supporting document that has a valid file type but is too large
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload one document that is too large
        Then I should see a 'file too large' error

    @lay-pfa-high-not-started
    Scenario: A user uploads one supporting document that has a valid file type then confirms they have no files to upload
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload one valid document
        Then the documents uploads page should contain the documents I uploaded
        When I have no further documents to upload
        Then I should be on the documents summary page
        When I change my mind and confirm I have no documents to upload
        Then I should see an 'answer could not be updated' error

    @lay-pfa-high-not-started
    Scenario: A user uploads one supporting document where the mimetype and file extension do not match
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload a file where the mimetype and file extension do not match
        Then I should see a 'mimetype and file type do not match' error
