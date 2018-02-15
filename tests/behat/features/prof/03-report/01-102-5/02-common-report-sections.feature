Feature: PROF user edits common report sections common to ALL report types

  Scenario: PROF-102-5 sections check
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    Then I should see a "#edit-contacts" element
    And I should see a "#edit-decisions" element
    And I should see a "#edit-visits_care" element
    And I should see a "#edit-balance" element
    And I should see a "#edit-bank_accounts" element
    And I should see a "#edit-money_transfers" element
    And I should see a "#edit-money_in" element
    And I should see a "#edit-money_out" element
    And I should see a "#edit-assets" element
    And I should see a "#edit-debts" element
    And I should see a "#edit-gifts" element
    And I should see a "#edit-other_info" element
    And I should see a "#edit-actions" element
    #And I should see a "#edit-pa_fee_expense" element
    And I should see a "#edit-documents" element

  Scenario: PROF 102-5 user edit decisions section
    Given I load the application status from "prof-team-users-complete"
    And I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
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

  Scenario: PROF 102-5 saves a contact
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    And I click on "edit-contacts, start"
        # chose "no records"
    Given the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | contact_exist_hasContacts_1 | no |
      | contact_exist_reasonForNoContacts | rfnc |


  Scenario: PROF 102-5 visits and care steps
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
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

  Scenario: PROF 102-5 report actions
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
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

  Scenario: PROF 102-5 any other info
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    And I click on "edit-other_info, start"
     # step 1
    And the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | more_info_actionMoreInfo_0      | yes  |
      | more_info_actionMoreInfoDetails | amid |

  Scenario: PROF adds documents to 102-5
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    # Check report is not submittable until documents section complete
    And the PROF report should not be submittable
    And I click on "edit-documents, start"
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

  Scenario: PROF deletes document from 102-5
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
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
    And I click on "document-delete"
    Then the form should be valid
    And the URL should match "/report/\d+/documents/step/1"
    # Check document removed
    And the step with the following values CAN be submitted:
      | document_wishToProvideDocumentation_0 | no |
    And I save the application status into "102-5-common-sections-complete"
