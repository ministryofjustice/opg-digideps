Feature: Report 103 start

  @deputy @deputy-103
  Scenario: Check 103 report not initially submittable
    Given I am logged in as "behat-lay-deputy-103@publicguardian.gov.uk" with password "Abcd1234"
    When I set the report start date to "1/1/2016"
    And I set the report end date to "31/12/2016"
    And I click on "report-start"
    # assert all tabs available
    Then I should see the "edit-decisions" link
    Then I should see the "edit-contacts" link
    Then I should see the "edit-visits_care" link
    Then I should see the "edit-deputy_expenses" link
    Then I should see the "edit-gifts" link
    Then I should see the "edit-bank_accounts" link
    Then I should not see the "edit-money_transfers" link
    Then I should see the "edit-money_in_short" link
    Then I should see the "edit-money_out_short" link
    Then I should see the "edit-assets" link
    Then I should see the "edit-debts" link
    Then I should see the "edit-actions" link
    Then I should see the "edit-other_info" link
    Then I should see the "edit-documents" link
    # check not submittable (as 103 money section it not completed yet)
    Then the lay report should not be submittable

  @deputy @deputy-103
  Scenario: Complete previously tested report sections
    Given I am logged in as "behat-lay-deputy-103@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "report-start"
    # Decisions
    When I click on "edit-decisions, start"
    And I fill in "mental_capacity_hasCapacityChanged_1" with "stayedSame"
    And I click on "save-and-continue"
    And I fill in "mental_assessment_mentalAssessmentDate_month" with "12"
    And I fill in "mental_assessment_mentalAssessmentDate_year" with "2015"
    And I click on "save-and-continue"
    And I fill in "decision_exist_hasDecisions_1" with "no"
    And I fill in "decision_exist_reasonForNoDecisions" with "Nothing happened"
    And I click on "save-and-continue, breadcrumbs-report-overview"
    # Contacts
    And I click on "edit-contacts, start"
    And I fill in "contact_exist_hasContacts_1" with "no"
    And I fill in "contact_exist_reasonForNoContacts" with "Nothing happened"
    And I click on "save-and-continue, breadcrumbs-report-overview"
    # Visits and care
    And I click on "edit-visits_care, start"
    And I fill in "visits_care_doYouLiveWithClient_0" with "yes"
    And I click on "save-and-continue"
    And I fill in "visits_care_doesClientReceivePaidCare_1" with "no"
    And I click on "save-and-continue"
    And I fill in "visits_care_whoIsDoingTheCaring" with "Family members"
    And I click on "save-and-continue"
    And I fill in "visits_care_doesClientHaveACarePlan_1" with "no"
    And I click on "save-and-continue, breadcrumbs-report-overview"
    # Accounts
    And I click on "edit-bank_accounts, start"
    And I fill in "account_accountType_0" with "current"
    And I click on "save-and-continue"
    And I fill in "account_bank" with "Great Bank"
    And I fill in "account_accountNumber" with "01ca"
    And I fill in "account_sortCode_sort_code_part_1" with "11"
    And I fill in "account_sortCode_sort_code_part_2" with "22"
    And I fill in "account_sortCode_sort_code_part_3" with "33"
    And I fill in "account_isJointAccount_1" with "no"
    And I click on "save-and-continue"
    And I fill in "account_openingBalance" with "25000"
    And I fill in "account_closingBalance" with "25000"
    And I click on "save-and-continue"
    And I fill in "add_another_addAnother_1" with "no"
    And I click on "save-and-continue, breadcrumbs-report-overview"
    # Expenses
    And I click on "edit-deputy_expenses, start"
    And I fill in "yes_no_paidForAnything_1" with "no"
    And I click on "save-and-continue, breadcrumbs-report-overview"
    # Gifts
    And I click on "edit-gifts, start"
    And I fill in "yes_no_giftsExist_1" with "no"
    And I click on "save-and-continue, breadcrumbs-report-overview"
    # Assets
    And I click on "edit-assets, start"
    And I fill in "yes_no_noAssetToAdd_1" with "1"
    And I click on "save-and-continue, breadcrumbs-report-overview"
    # Debts
    And I click on "edit-debts, start"
    And I fill in "yes_no_hasDebts_1" with "no"
    And I click on "save-and-continue, breadcrumbs-report-overview"
    # Actions
    And I click on "edit-actions, start"
    And I fill in "action_doYouExpectFinancialDecisions_1" with "no"
    And I click on "save-and-continue"
    And I fill in "action_doYouHaveConcerns_1" with "no"
    And I click on "save-and-continue, breadcrumbs-report-overview"
    # Any other info
    And I click on "edit-other_info, start"
    And I fill in "more_info_actionMoreInfo_1" with "no"
    And I click on "save-and-continue, breadcrumbs-report-overview"
    # Documents
    And I click on "edit-documents, start"
    And I fill in "document_wishToProvideDocumentation_1" with "no"
    And I click on "save-and-continue, breadcrumbs-report-overview"
    # Assert that more info is still needed
    Then the lay report should not be submittable

