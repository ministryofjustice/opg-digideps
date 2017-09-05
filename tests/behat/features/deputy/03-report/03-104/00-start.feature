Feature: Report 104 start

  @deputy
  Scenario: load app status taken after 102 non-financial sections are completed
    Given I load the application status from "report-decisions-contacts-visitscare-actions-info"
    And I change the report 1 type to "104"

  @deputy
  Scenario: test tabs for 104
    And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016"
    #Then the report should not be submittable
    Then I should see the "edit-decisions" link
    Then I should see the "edit-contacts" link
    Then I should see the "edit-visits_care" link
    Then I should see the "edit-lifestyle" link
    Then I should see the "edit-actions" link
    Then I should see the "edit-other_info" link
    Then I should see the "edit-documents" link
    #Then I should see the "edit-hw" link
    # Assert finance sections are NOT displayed
    Then I should not see the "edit-debts" link
    Then I should not see the "edit-bank_accounts" link
    Then I should not see the "edit-money_in" link
    Then I should not see the "edit-money_out" link
    Then I should not see the "edit-money_transfers" link
    Then I should not see the "edit-money_in_short" link
    Then I should not see the "edit-money_out_short" link





