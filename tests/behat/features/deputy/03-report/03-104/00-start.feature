Feature: Report 104 start

  @deputy
  Scenario: load app status taken after 102 non-financial sections are completed
    Given I load the application status from "report-decisions-contacts-visitscare-actions-info"
    And I change the report of the client with case number "behat001" to "104"

  @deputy
  Scenario: test tabs for 104
    Given I am logged in as "behat-user@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "report-start"
    Then the lay report should not be submittable
    And I should see the "edit-decisions" link
    And I should see the "edit-contacts" link
    And I should see the "edit-visits_care" link
    And I should see the "edit-lifestyle" link
    And I should see the "edit-actions" link
    And I should see the "edit-other_info" link
    And I should see the "edit-documents" link
    #Then I should see the "edit-hw" link
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







