@v2 @report-overview
Feature: Report Overview - All User Roles

@lay-pfa-high-not-started
  Scenario: A Lay Deputy has not started a report
    Given a Lay Deputy has not started a report
    When I view and start the documents report section
    And I have no documents to upload
    Then I should be on the documents summary page
    And the documents summary page should not contain any documents

@prof-admin-not-started
  Scenario: A user uploads one supporting document that has a valid file type
    Given a Lay Deputy has not started a report
    When I view and start the documents report section
    And I have documents to upload
    And I upload one valid document
    Then the documents uploads page should contain the documents I uploaded
    When I have no further documents to upload
    Then I should be on the documents summary page
    And the documents summary page should contain the documents I uploaded

@prof-team-hw-not-started
  Scenario: A user uploads multiple supporting document that have valid file types
    Given a Lay Deputy has not started a report
    When I view and start the documents report section
    And I have documents to upload
    And I upload multiple valid documents
    Then the documents uploads page should contain the documents I uploaded
    When I have no further documents to upload
    Then I should be on the documents summary page
    And the documents summary page should contain the documents I uploaded
