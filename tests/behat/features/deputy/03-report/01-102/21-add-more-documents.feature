Feature: Add more documents after report has been submitted

  @deputy @shaun
  Scenario: Deputy adds documents after submission
    Given I load the application status from "report-submit-reports"
    And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I save the current URL as "your-reports"
    And I click on "add-more-documents" in the "submitted-reports" region
    Then the URL should match "report/\d+/documents/step/2"
    And each text should be present in the corresponding region:
      | file1.pdf        | previous-submitted-document-list |
      | good.jpg        | previous-submitted-document-list |
      | good.png        | previous-submitted-document-list |

    # check duplicate file error
    When I attach the file "good.jpg" to "report_document_upload_file"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_file   |

    # add brand new file
    When I attach the file "small.jpg" to "report_document_upload_file"
    And I click on "attach-file"
    Then the form should be valid
    And each text should be present in the corresponding region:
      | small.jpg        | new-document-list |

    Then I click on "continue-to-submit"
    Then the URL should match "report/\d+/documents/submit-more"
    And each text should be present in the corresponding region:
      | small.jpg        | new-document-list |
    Then I click on "confirm-submit"
    And I go to the URL previously saved as "your-reports"
