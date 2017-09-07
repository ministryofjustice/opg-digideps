Feature: Report 103 start

  @deputy
  Scenario: load app status to common sections completed, change type to 103 and check not submittable
    # Since 103 shares same section as 102, import status from 102 before money section (that is the only different section) were added
    # that checkpoint correspond to a 103 report without money added
    Given I load the application status from "102-common-sections-complete"
    And I change the report 1 type to "103"
    # assert not submittable yet
    And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016"
    #test tabs
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
    Then the report should not be submittable



