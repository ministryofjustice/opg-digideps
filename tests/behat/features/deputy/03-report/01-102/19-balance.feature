Feature: Report balance

    @deputy
    Scenario: balance fix
        Given I save the application status into "report-balance-before"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        # assert report not submittable
        And I click on "reports, report-2016"
        Then the report should not be submittable
        # check balance mismatch difference
        When I click on "balance-view-details"
        Then I should see the "balance-bad" region
        And I should see "£191.11" in the "unaccounted-for" region
        # fix balance
        And I save the application status into "balance-before-adding-explanation"
        And I click on "step-back, edit-bank_accounts"
        And I click on "edit" in the "account-11cf" region
        And I submit the step
        And I submit the step
        And the step with the following values CAN be submitted:
            | account_closingBalance | 43.89 |
        And I click on "breadcrumbs-report-overview"
        # assert balance is now good
        Then I should not see the "balance-bad" region
        # assert report can be sumbmitted
        # When I set the report 1 end date to 3 days ago
        Then the report should be submittable

    @deputy
    Scenario: balance explanation
        # restore previous bad balance, add explanation
        Given I save the application status into "report-balance-explanation-before"
        And I load the application status from "balance-before-adding-explanation"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports, report-2016"
        And I click on "balance-view-details"
        And I should see the "balance-bad" region
        # add explanation
        Then the step cannot be submitted without making a selection
        And the step with the following values CANNOT be submitted:
            | balance_balanceMismatchExplanation    | short | [ERR] |
        And the step with the following values CAN be submitted:
            | balance_balanceMismatchExplanation    | lost 110 pounds on the road |
        And I should not see the "balance-view-details" link
        And the report should be submittable

        