Feature: Report 104 start

  @deputy @104
  Scenario: load app status to common sections completed, change type to 104 and check not submittable
    Given I load the application status from "102-common-sections-complete"
    And I change the report 7 type to "104"


  @deputy @104
  Scenario: test tabs for 104
    # assert not submittable yet
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    # click on 104 report
    And I click on "pa-report-open" in the "client-1000014" region
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



