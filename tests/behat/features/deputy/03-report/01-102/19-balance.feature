Feature: Report balance

    @deputy
    Scenario: balance fix
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        # assert report not submittable
        And I click on "report-start"
        #And I should see an "#finances-section .behat-alert-message" element
        And the lay report should not be submittable
        # check balance mismatch difference
        When I click on "edit-balance"
        Then I should see the "balance-bad" region
        And I should see "£191.11" in the "unaccounted-for" region
        # fix balance (adding closing bank account)
        And I save the application status into "balance-before-adding-explanation"
        And I click on "breadcrumbs-report-overview, edit-bank_accounts"
        And I click on "edit" in the "account-11cf" region
        And I submit the step
        And I submit the step
        And the step with the following values CAN be submitted:
            | account_closingBalance | 43.89 |
        And I click on "breadcrumbs-report-overview"
        # assert balance is now good
        Then I should see the "report-ready-banner" region
        Then I should see the "balance-state-done" region
        # assert report can be sumbmitted
        # When I set the report 1 end date to 3 days ago
        Then the lay report should be submittable

    @deputy
    Scenario: balance explanation
        # restore previous bad balance, add explanation
        Given I load the application status from "balance-before-adding-explanation"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "report-start, edit-balance"
        And I should see the "balance-bad" region
        # add explanation
        Then the step cannot be submitted without making a selection
        And the step with the following values CANNOT be submitted:
            | balance_balanceMismatchExplanation    | short | [ERR] |
        And the step with the following values CAN be submitted:
            | balance_balanceMismatchExplanation    | lost 110 pounds on the road |
        Then the URL should match "report/\d+/overview"
        Then I should see the "report-ready-banner" region
        Then I should see the "balance-state-explained" region
        And the lay report should be submittable

    @deputy @shaun
    Scenario: Transactions CSV link
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I click on "report-start, edit-balance"
        And I click on "download-transactions"
        And the response status code should be 200
        And the response should have the "Content-Type" header containing "text/csv"
        And the response should have the "Content-Disposition" header containing ".csv"
        And the response should contain "Type,Category,Amount"
