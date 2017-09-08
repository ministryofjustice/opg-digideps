Feature: Report submit (client 1000011)

    @103
    Scenario: Submit 103 report
        Given I load the application status from "pa-report-103-inprogress"
        And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000014" region
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
        And I click on "edit-report_submit, declaration-page"
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

