@v2 @v2_sequential_3 @documents
Feature: Documents - All User Roles

    @lay-pfa-high-not-started @documents-no-more-to-upload
    Scenario: A user uploads one supporting document that has a valid file type then confirms they have no files to upload
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload one valid document with the filename "good-image.jpg"
        Then the document uploads page should contain a document with the filename "good_image.jpg"
        When I have no further documents to upload
        Then I should be on the documents summary page
        When I change my mind and confirm I have no documents to upload
        Then I should see an 'answer could not be updated' error

    @lay-pfa-high-not-started @documents-mime-and-file-types-mismatch
    Scenario: A user uploads one supporting document where the mimetype and file extension do not match
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload a file where the mimetype and file extension do not match
        Then I should see a 'mimetype and file type do not match' error

    @lay-pfa-high-not-started @documents-upload-same-file-twice
    Scenario: A user uploads one supporting document that has a valid file type then tries to upload the same file again
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload one valid document with the filename "good-image.jpg"
        Then the document uploads page should contain a document with the filename "good_image.jpg"
        When I upload one valid document with the filename "good-image.jpg"
        Then I should see a 'duplicate file name' error
