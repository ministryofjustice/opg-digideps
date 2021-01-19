Feature: PA user edits common report sections common to ALL report types

#  Scenario: Setup data
#    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
#    Given the following court orders exist:
#      | client   | deputy      | deputy_type | report_type                                | court_date |
#      | 78978978 | KimPetras   | PA          | Property and Financial Affairs High Assets | 2018-01-30 |
#      | 98798798 | JamesShaw   | PA_ADMIN    | High Assets with Health and Welfare        | 2018-01-30 |

  @102 @103-6 @104
  Scenario: PA 102 user edit decisions section
    And I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    And I fill in "search" with "02100014"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-02100014" region
    Then the response status code should be 200
    And the URL should match "report/\d+/overview"
    And I click on "edit-decisions, start"
        # step  mental capacity
    Then the step with the following values CAN be submitted:
      | mental_capacity_hasCapacityChanged_1 | stayedSame |
    And the step with the following values CAN be submitted:
      | mental_assessment_mentalAssessmentDate_month | 01 |
      | mental_assessment_mentalAssessmentDate_year | 2017 |
        # chose "no records"
    Given the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | decision_exist_hasDecisions_1 | no |
      | decision_exist_reasonForNoDecisions | rfnd |

  @102 @103-6 @104
  Scenario: PA 102 saves a contact
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    And I fill in "search" with "02100014"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-02100014" region
    And I click on "edit-contacts, start"
        # chose "no records"
    Given the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | contact_exist_hasContacts_1 | no |
      | contact_exist_reasonForNoContacts | rfnc |

  @102 @103-6 @104
  Scenario: PA 102 visits and care steps
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    And I fill in "search" with "02100014"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-02100014" region
    And I click on "edit-visits_care, start"
    # step 1 empty
    And the step cannot be submitted without making a selection
    # step 1 missing details
    And the step with the following values CAN be submitted:
      | visits_care_doYouLiveWithClient_1      | no    |
      | visits_care_howOftenDoYouContactClient | daily |
    # step 2 empty
    And the step cannot be submitted without making a selection
    # step 2 correct
    And the step with the following values CAN be submitted:
      | visits_care_doesClientReceivePaidCare_0 | yes                 |
      | visits_care_howIsCareFunded_0           | client_pays_for_all |
    # step 3 empty
    And the step cannot be submitted without making a selection
    # step 3 correct
    And the step with the following values CAN be submitted:
      | visits_care_whoIsDoingTheCaring | the brother |
    # step 4 empty
    And the step cannot be submitted without making a selection
    # step 4 correct
    Then the step with the following values CAN be submitted:
      | visits_care_doesClientHaveACarePlan_0         | yes  |
      | visits_care_whenWasCarePlanLastReviewed_month | 12   |
      | visits_care_whenWasCarePlanLastReviewed_year  | 2015 |

  @102 @103-6 @104
  Scenario: PA 102 report actions
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    And I fill in "search" with "02100014"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-02100014" region
    And I click on "edit-actions, start"
      # step 1
    And the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | action_doYouExpectFinancialDecisions_0      | yes    |
      | action_doYouExpectFinancialDecisionsDetails | dyefdd |
    # step 2
    And the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | action_doYouHaveConcerns_0      | yes   |
      | action_doYouHaveConcernsDetails | dyhcd |

  @102 @103-6 @104
  Scenario: PA 102 any other info
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    And I fill in "search" with "02100014"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-02100014" region
    And I click on "edit-other_info, start"
     # step 1
    And the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | more_info_actionMoreInfo_0      | yes  |
      | more_info_actionMoreInfoDetails | amid |

  @102 @103-6 @104
  Scenario: PA adds documents to 102
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    And I fill in "search" with "02100014"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-02100014" region
    # Check report is not submittable until documents section complete
    And the PA report should not be submittable
    And I click on "edit-documents, start"
    # chose "yes documents"
    Then the URL should match "report/\d+/documents"
    And the step with the following values CAN be submitted:
      | document_wishToProvideDocumentation_0 | yes |
    # check empty file error
    When I attach the file "empty-file.pdf" to "report_document_upload_files"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_files   |
    # check not a pdf file error
    When I attach the file "not-a-pdf.pdf" to "report_document_upload_files"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_files   |

    When I attach the file "good.pdf" to "report_document_upload_files"
    And I click on "attach-file"
    Then the form should be valid
    And each text should be present in the corresponding region:
      | good.pdf        | document-list |
    # check duplicate file error
    When I attach the file "good.pdf" to "report_document_upload_files"
    And I click on "attach-file"
    Then the following fields should have an error:
      | report_document_upload_files   |


  @102 @103-6 @104
  Scenario: PA deletes document from 102
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    And I fill in "search" with "02100014"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-02100014" region
    And I click on "edit-documents"
    # chose "yes documents"
    Then the URL should match "report/\d+/documents/summary"
    And I save the current URL as "document-summary"
    And I click on "delete-documents-button" in the "document-list" region
    Then the URL should match "/documents/\d+/delete"
    # test cancel button on confirmation page
    When I click on "confirm-cancel"
    Then I go to the URL previously saved as "document-summary"
    And I click on "delete-documents-button" in the "document-list" region
    Then the response status code should be 200
    # delete this time
    And I click on "confirm"
    Then the form should be valid
    And the URL should match "/report/\d+/documents/step/1"
    # Check document removed
    And the step with the following values CAN be submitted:
      | document_wishToProvideDocumentation_0 | no |
