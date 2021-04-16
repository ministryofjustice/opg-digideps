Feature: Report balance

    @deputy
    Scenario: balance fix
        Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "DigidepsPass1234"
        # assert report not submittable
        And I click on "report-start"
        #And I should see an "#finances-section .behat-alert-message" element
        And the lay report should not be submittable
        # check balance mismatch difference
        When I click on "edit-balance"
        Then I should see the "balance-bad" region
        And I should see "£193.11" in the "unaccounted-for" region
        # fix balance (adding closing bank account)
        And I save the application status into "balance-before-adding-explanation"
        And I click on "breadcrumbs-report-overview, edit-bank_accounts"
        And I click on "edit" in the "account-11cf" region
        And I submit the step
        And I submit the step
        And the step with the following values CAN be submitted:
            | account_closingBalance | 41.89 |
        And I click on "breadcrumbs-report-overview"
        # assert balance is now good
        Then I should see "Your report is ready to submit."
        Then I should see the "balance-state-done" region
        # assert report can be sumbmitted
        Then the lay report should be submittable

    @deputy
    Scenario: balance explanation
        # restore previous bad balance, add explanation
        Given I load the application status from "balance-before-adding-explanation"
        And I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "DigidepsPass1234"
        And I click on "report-start, edit-balance"
        And I should see the "balance-bad" region
        # add explanation
        Then the step cannot be submitted without making a selection
        And the step with the following values CANNOT be submitted:
            | balance_balanceMismatchExplanation    | short | [ERR] |
        And the step with the following values CAN be submitted:
            | balance_balanceMismatchExplanation    | lost 110 pounds on the road |
        Then the URL should match "report/\d+/overview"
        Then I should see "Your report is ready to submit."
        Then I should see the "balance-state-explained" region
        And the lay report should be submittable

    @deputy
    Scenario: Transactions CSV link
        Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "DigidepsPass1234"
        When I click on "report-start, edit-balance"
        And I click on "download-transactions"
        And the response status code should be 200
        And the response should have the "Content-Type" header containing "text/csv"
        And the response should have the "Content-Disposition" header containing ".csv"
        And the response should contain "Type,Category,Amount"
