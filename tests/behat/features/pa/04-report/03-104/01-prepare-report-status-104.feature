Feature: Report 104 start

  @deputy
  Scenario: load app status to common sections completed, change type to 104 and check not submittable
    Given I load the application status from "102-common-sections-complete"
    And I change the report 1 type to "104"
    # assert not submittable yet
    And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016"
    #test tabs
    Then I should see the "edit-contacts" link
    Then I should see the "edit-visits_care" link
    Then I should see the "edit-actions" link
    Then I should see the "edit-other_info" link
    Then I should see the "edit-documents" link
    Then I should not see the "edit-deputy-expenses" link
    Then I should not see the "edit-gifts" link
    Then I should not see the "edit-bank-accounts" link
    Then I should not see the "edit-money_transfers" link
    Then I should not see the "edit-money_in_short" link
    Then I should not see the "edit-money_out_short" link
    Then I should not see the "edit-assets" link
    Then I should not see the "edit-debts" link

    # check not submittable (as 104 lifestyle it not completed yet)
    Then the report should not be submittable



