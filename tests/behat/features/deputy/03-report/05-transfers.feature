Feature: deputy / report / account transfers

    @deputy
    Scenario: money in 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the accounts page of the "2016" report
        And I follow "account-transfers"
        # wrong values (wrong amount types, amount without explanation, explanation without amount)
        When I fill in the following:
            | transfers_accountFromId |  |
            | transfers_accountToId   |  |
            | transfers_amount        |  |
        And I press "transfers_save"
        Then the following fields should have an error:
            | transfers_accountFromId |
            | transfers_accountToId   |
            | transfers_amount        |
        And I save the page as "report-account-transfers-errors"
        # right values
        When I fill in the following:
            | transfers_accountFromId | 1 |
            | transfers_accountToId   | 2 |
            | transfers_amount        | 1200 |
        And I press "transfers_save"
        Then the form should be valid
        Then I should see the "transfer-n-1" region
        # delete
        When I press "delete-button"
        Then I should not see the "transfer-n-1" region
        # tick no transfers
        Given the checkbox "report_no_transfers_noTransfersToAdd" is not checked
        When I check "report_no_transfers_noTransfersToAdd"
        And I press "report_no_transfers_saveNoTransfer"
        Then the checkbox "report_no_transfers_noTransfersToAdd" should not be checked 
        

    @deputy
    Scenario: money out 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the accounts page of the "2016" report
        And I follow "account-moneyout"
        # check no data was previously saved 
        Then the following fields should have the corresponding values:
            | transfers_transfersOut_0_amount        |  |
            | transfers_transfersOut_11_amount       |  |
            | transfers_transfersOut_11_moreDetails  |  |
        And I save the page as "report-account-transfers-empty"
        # wrong values (wrong amount types, amount without explanation, explanation without amount)
        When I fill in the following:
            | transfers_transfersOut_0_amount        | in |
            | transfers_transfersOut_4_amount        | 10000000001 |
        And I press "transfers_save"
        Then the following fields should have an error:
            | transfers_transfersOut_0_amount  |
            | transfers_transfersOut_4_amount  |
        And I save the page as "report-account-transfers-errors"
        # right values
        When I fill in the following:
            | transfers_transfersOut_0_amount       | 1250 |
            | transfers_transfersOut_1_amount       |  |
            | transfers_transfersOut_2_amount       |  |
            | transfers_transfersOut_3_amount       |  |
            | transfers_transfersOut_4_amount       |  |
            | transfers_transfersOut_11_amount      | 2100.0 |
            | transfers_transfersOut_11_moreDetails | more-details-in-15  |
        And I press "transfers_save"
        Then the form should be valid
        # reload page
        And I follow "account-moneyout"
        # assert value saved
        And the following fields should have the corresponding values:
            | transfers_transfersOut_0_amount       | 1,250.00 |
            | transfers_transfersOut_11_amount      | 2,100.00 |
            | transfers_transfersOut_11_moreDetails | more-details-in-15  |
        And I should see "3,350.00" in the "transaction-total" region
        And I save the page as "report-account-transfers-data-saved"