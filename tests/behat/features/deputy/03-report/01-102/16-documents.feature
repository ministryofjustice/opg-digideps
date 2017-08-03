Feature: Report documents

  @deputy
  Scenario: No documents to attach
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-documents, start"
    # chose "no documents"
    Then the URL should match "report/\d+/documents/step/1"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | document_wishToProvideDocumentation_1 | no |
    # check no documents in summary page
    Then the URL should match "report/\d+/documents/summary"
    And each text should be present in the corresponding region:
      | No documents        | document-list |

  @deputy
  Scenario: Edit documents to attach
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-documents"
    # chose "yes documents"
    Then the URL should match "report/\d+/documents"
    And the step with the following values CAN be submitted:
      | document_wishToProvideDocumentation_0 | yes |
    # check empty file error
    When I attach the file "empty-file.pdf" to "report_document_upload_file"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_file   |

    # check not a pdf file error
    When I attach the file "not-a-pdf.pdf" to "report_document_upload_file"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_file   |

    # check vba-eicar file error TO ENABLE ONCE FILE SCANNER ENABLED
    When I attach the file "pdf-doc-vba-eicar-dropper.pdf" to "report_document_upload_file"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_file   |

    When I attach the file "good.pdf" to "report_document_upload_file"
    And I click on "attach-file"
    Then the form should be valid
    And each text should be present in the corresponding region:
      | good.pdf        | document-list |
    # check duplicate file error
    When I attach the file "good.pdf" to "report_document_upload_file"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_file   |

  @deputy
  Scenario: Delete document
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-documents"
    # chose "yes documents"
    Then the URL should match "report/\d+/documents"
    And the step with the following values CAN be submitted:
      | document_wishToProvideDocumentation_0 | yes |
    And I save the current URL as "document-list"
    And I click on "delete-documents-button" in the "document-list" region
    Then the URL should match "/documents/\d+/delete"
    # test cancel button on confirmation page
    When I click on "confirm-cancel"
    Then I go to the URL previously saved as "document-list"
    And I click on "delete-documents-button" in the "document-list" region
    Then the response status code should be 200
    # delete this time
    And I click on "document-delete"
    Then the form should be valid
    Then I go to the URL previously saved as "document-list"
    # Check document removed
    And I should not see "good.pdf" in the "document-list" region
    And I click on "cancel"
    Then the URL should match "report/\d+/overview"
    And the report should not be submittable
    Then I click on "reports, report-2016, edit-documents"
    # chose "no documents" to make report submittable
    Then the URL should match "report/\d+/documents"
    And the step with the following values CAN be submitted:
      | document_wishToProvideDocumentation_0 | no |

    @deputy
    Scenario: Upload file1.pdf and file2.pdf
      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
      And I click on "reports, report-2016, edit-documents"
      And the step with the following values CAN be submitted:
        | document_wishToProvideDocumentation_0 | yes |
      When I attach the file "file1.pdf" to "report_document_upload_file"
      And I click on "attach-file"
      And I attach the file "file2.pdf" to "report_document_upload_file"
      And I click on "attach-file"
      Then each text should be present in the corresponding region:
        | file1.pdf        | document-list |
        | file2.pdf        | document-list |
