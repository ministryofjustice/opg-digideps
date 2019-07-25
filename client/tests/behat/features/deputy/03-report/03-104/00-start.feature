Feature: Report 104 start

  @deputy @deputy-104
  Scenario: test tabs for 104
    Given I am logged in as "behat-lay-deputy-104@publicguardian.gov.uk" with password "Abcd1234"
    When I set the report start date to "1/1/2016"
    And I set the report end date to "31/12/2016"
    And I click on "report-start"
    # assert all tabs available
    And I should see the "edit-decisions" link
    And I should see the "edit-contacts" link
    And I should see the "edit-visits_care" link
    And I should see the "edit-lifestyle" link
    And I should see the "edit-actions" link
    And I should see the "edit-other_info" link
    And I should see the "edit-documents" link
    # Assert finance sections are NOT displayed
    And I should not see the "edit-debts" link
    And I should not see the "edit-bank_accounts" link
    And I should not see the "edit-money_in" link
    And I should not see the "edit-money_out" link
    And I should not see the "edit-money_transfers" link
    And I should not see the "edit-money_in_short" link
    And I should not see the "edit-money_out_short" link
    And I should not see the "edit-gifts" link
    And the lay report should not be submittable

  @deputy @deputy-104
  Scenario: Complete previously tested report sections
    Given I am logged in as "behat-lay-deputy-104@publicguardian.gov.uk" with password "Abcd1234"
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
