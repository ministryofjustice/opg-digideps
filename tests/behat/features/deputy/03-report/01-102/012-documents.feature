Feature: Report documents

  @deputy
  Scenario: No documents to attach
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-documents, start"
    And the
    # chose "no documents"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_wishToProvideDocuments_1 | no |
    # check no documents in summary page
    And each text should be present in the corresponding region:
      | No documents        | document-list |
