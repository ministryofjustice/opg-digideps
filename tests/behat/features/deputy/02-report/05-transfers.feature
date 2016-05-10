Feature: deputy / report / account transfers

    @deputy
    Scenario: account transfers
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open"
        And I follow "edit-accounts"
        And I follow "account-transfers"
        # wrong values (wrong amount types, amount without explanation, explanation without amount)
        When I press "transfers_save"
        Then the following fields should have an error:
            | transfers_accountFromId |
            | transfers_accountToId   |
            | transfers_amount        |
        And I save the page as "report-account-transfers-errors"
        # right values
        When I fill in "transfers_amount" with "1200"
        And I select "HSBC main account Current account (****0876)" from "transfers_accountFromId"
        And I select "temp ISA (****8888)" from "transfers_accountToId"
        And I press "transfers_save"
        Then the form should be valid
        Then I should see the "transfer" region
        # delete
        When I click on "delete-confirm"
        Then I should not see the "transfer" region
        # no transfers
        Given the checkbox "report_no_transfers_noTransfersToAdd" is not checked
        When I check "report_no_transfers_noTransfersToAdd"
        And I press "report_no_transfers_saveNoTransfer"
        Then the checkbox "report_no_transfers_noTransfersToAdd" should be checked 