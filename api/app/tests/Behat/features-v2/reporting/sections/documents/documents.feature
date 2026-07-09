@v2 @v2_sequential_3 @documents
Feature: Documents - All User Roles

    @lay-pfa-high-not-started @documents-upload-same-file-twice
    Scenario: A user uploads one supporting document that has a valid file type then tries to upload the same file again
        Given a Lay Deputy has not started a report
        When I view and start the documents report section
        And I have documents to upload
        And I upload one valid document with the filename "good-image.jpg"
        Then the document uploads page should contain a document with the filename "good_image.jpg"
        When I upload one valid document with the filename "good-image.jpg"
        Then I should see a 'duplicate file name' error
