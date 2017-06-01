Feature: Report submit

    Scenario: report declaration page
        Given I load the application status from "pa-report-completed"
        And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "tab-ready"
        And I click on "pa-report-open" in the "client-1000014" region
        Then I should not see the "download-2016-report" link
        # if not found, it means that the report is not submittable
        And I click on "report-submit"
        Then the URL should match "/report/\d+/review"
        And I click on "declaration-page"
        Then the URL should match "/report/\d+/declaration"
        And I save the page as "report-submit-declaration"

    Scenario: report submission
        Given emails are sent from "deputy" area
        And I reset the email log
        And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000014" region
        And I click on "report-submit, declaration-page"
        When I fill in the following:
            | report_declaration_agree | 1 |
            | report_declaration_agreedBehalfDeputy_0 | only_deputy |
            | report_declaration_agreedBehalfDeputyExplanation |  |
        And I press "report_declaration_save"
        Then the form should be valid
        And the URL should match "/report/\d+/submitted"
        And I save the page as "report-submit-submitted"
        And I should not see the "report-submit-submitted" link
        # assert report display page is not broken
        When I click on "return-to-pa-dashboard"
        Then the URL should match "/pa"
        And the response status code should be 200
        And the last email should contain "Thank you for submitting"
        And the last email should have been sent to "behat-pa1@publicguardian.gsi.gov.uk"

    Scenario: assert 2nd year report has been created
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "tab-in-progress"
        And I click on "pa-report-open" in the "client-1000014" region
        Then I should see a "#edit-contacts" element
        And I should see a "#edit-decisions" element
        And I should see a "#edit-bank_accounts" element
        And I should see a "#edit-assets" element
        # check bank accounts are added again
        When I follow "edit-bank_accounts"
        Then each text should be present in the corresponding region:
            | HSBC - main account | account-01ca |
            | Current account     | account-01ca |
            | 112233              | account-01ca |
        And I save the application status into "pa-report-submitted"

    Scenario: Submit 103 report
        Given I load the application status from "pa-report-103-inprogress"
        And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000011" region
        And I click on "edit-decisions, start"
        Then the step with the following values CAN be submitted:
            | mental_capacity_hasCapacityChanged_1 | stayedSame |
        And the step with the following values CAN be submitted:
            | mental_assessment_mentalAssessmentDate_month | 01 |
            | mental_assessment_mentalAssessmentDate_year | 2017 |
        Given the step cannot be submitted without making a selection
        Then the step with the following values CAN be submitted:
            | decision_exist_hasDecisions_1 | no |
            | decision_exist_reasonForNoDecisions | rfnd |
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000011" region
        And I click on "edit-contacts, start"
        Given the step cannot be submitted without making a selection
        Then the step with the following values CAN be submitted:
            | contact_exist_hasContacts_1 | no |
            | contact_exist_reasonForNoContacts | rfnc |
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000011" region
        And I click on "edit-visits_care, start"
        And the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | visits_care_doYouLiveWithClient_1      | no    |
            | visits_care_howOftenDoYouContactClient | daily |
        And the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | visits_care_doesClientReceivePaidCare_0 | yes                 |
            | visits_care_howIsCareFunded_0           | client_pays_for_all |
        And the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | visits_care_whoIsDoingTheCaring | the brother |
        And the step cannot be submitted without making a selection
        Then the step with the following values CAN be submitted:
            | visits_care_doesClientHaveACarePlan_0         | yes  |
            | visits_care_whenWasCarePlanLastReviewed_month | 12   |
            | visits_care_whenWasCarePlanLastReviewed_year  | 2015 |
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000011" region
        And I click on "edit-actions, start"
        And the step cannot be submitted without making a selection
        Then the step with the following values CAN be submitted:
            | action_doYouExpectFinancialDecisions_0      | yes    |
            | action_doYouExpectFinancialDecisionsDetails | dyefdd |
        And the step cannot be submitted without making a selection
        Then the step with the following values CAN be submitted:
            | action_doYouHaveConcerns_0      | yes   |
            | action_doYouHaveConcernsDetails | dyhcd |
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000011" region
        And I click on "edit-other_info, start"
        And the step cannot be submitted without making a selection
        Then the step with the following values CAN be submitted:
            | more_info_actionMoreInfo_0      | yes  |
            | more_info_actionMoreInfoDetails | amid |
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000011" region
        And I click on "edit-pa_fee_expense, start"
        Given the step cannot be submitted without making a selection
        And the step with the following values cannot be submitted:
            | fee_exist_hasFees_1 | no |
        Given the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | fee_exist_reasonForNoFees | Some reason for no fees|
        Given the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | yes_no_paidForAnything_1 | no |
        And each text should be present in the corresponding region:
            | no                            | no-contacts        |
            | Some reason for no fees       | reason-no-fees     |
            | no                            | paid-for-anything  |
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000011" region
        And I click on "edit-gifts, start"
        Given the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | yes_no_giftsExist_1 | no |
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000011" region
        And I click on "edit-assets, start"
        And the step with the following values CAN be submitted:
            | yes_no_noAssetToAdd_1 | 1 |
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000011" region
        And I click on "edit-debts, start"
        Given the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | yes_no_hasDebts_1 | no |
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000011" region
        And I click on "edit-bank_accounts, start"
        And the step with the following values CAN be submitted:
            | account_accountType_0 | current |
        And the step with the following values CAN be submitted:
            | account_bank                      | HSBC - main account |
            | account_accountNumber             | 01ca                |
            | account_sortCode_sort_code_part_1 | 11                  |
            | account_sortCode_sort_code_part_2 | 22                  |
            | account_sortCode_sort_code_part_3 | 33                  |
            | account_isJointAccount_1          | no                  |
        And the step with the following values CAN be submitted:
            | account_openingBalance | 100.40 |
            | account_closingBalance | 100.40 |
        And I choose "no" when asked for adding another record
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000011" region
        Then the report should be submittable
        And I save the application status into "pa-report-103-ready-to-submit"

    Scenario: pa 103 report submission
        Given I load the application status from "pa-report-103-ready-to-submit"
        And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000011" region
        And I click on "report-submit, declaration-page"
        When I fill in the following:
            | report_declaration_agree | 1 |
            | report_declaration_agreedBehalfDeputy_0 | only_deputy |
            | report_declaration_agreedBehalfDeputyExplanation |  |
        And I press "report_declaration_save"
        Then the form should be valid
        And I save the application status into "pa-report-103-submitted"

    Scenario: assert 2nd year 103 pa report has been created
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "tab-in-progress"
        And I click on "pa-report-open" in the "client-1000011" region
        Then I should see a "#edit-money_in_short" element
        And I should see a "#edit-money_out_short" element
        And I save the application status into "pa-report-103-submitted"