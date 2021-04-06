@v2
Feature: Documents - All User Roles

  Scenario: A user has no supporting documents to add
    Given a Lay Deputy has not started a report
    When I view and start the documents report section
    And I have no documents to upload
    Then I should be on the documents summary page
    And the documents summary page should not contain any documents

  Scenario: A user uploads one supporting document that has a valid file type
    Given a Lay Deputy has not started a report
    When I view and start the documents report section
    And I have documents to upload
    And I upload one valid document
    Then the documents uploads page should contain the documents I uploaded
    When I have no further documents to upload
    Then I should be on the documents summary page
    And the documents summary page should contain the documents I uploaded

#  Scenario: A user uploads two supporting document that have valid file types
#    Given a Lay Deputy has not started a report
#    When I view and start the documents report section
#    And I upload two valid document
#    Then the documents uploads page should contain the documents I uploaded
#    When I have no more documents to upload
#    Then I should be on the documents summary page
#    And the documents summary page should contain the documents I uploaded
#
#  Scenario: A user deletes one supporting document that they uploaded from the uploads page
#    Given a Lay Deputy has not started a report
#    When I view and start the documents report section
#    And I upload one valid document
#    And I remove the document I uploaded
#    When I have no more documents to upload
#    Then I should be on the documents summary page
#    When I delete the document I uploaded
#    Then I should be on the documents start page
#
#  Scenario: A user deletes one supporting document that they uploaded from the summary page
#    Given a Lay Deputy has not started a report
#    When I view and start the documents report section
#    And I upload one valid document
#    And I remove the document I uploaded
#    When I have no more documents to upload
#    Then I should be on the documents summary page
#    When I delete the document I uploaded
#    Then I should be on the documents start page
#
#  Scenario: A user uploads one supporting document that has an invalid file type
#    Given a Lay Deputy has not started a report
#    When I view and start the documents report section
#    And I upload one invalid document
#    Then I should see an invalid file type error

#  Scenario: A user uploads one supporting document that has a valid file type but is too large
#    Given a Lay Deputy has not started a report
#    When I view and start the documents report section
#    And I upload one document that is too large
#    Then I should see a file too large error
#
#  Scenario: A user uploads one supporting document that has a valid file type then confirms they have no files to upload
#    Given a Lay Deputy has not started a report
#    When I view and start the documents report section
#    And I upload one valid document
#    Then the documents uploads page should contain the documents I uploaded
#    When I have no more documents to upload
#    Then I should be on the documents summary page
#    When I confirm I have no documents to upload
#    Then I should see an answer could not be updated error
