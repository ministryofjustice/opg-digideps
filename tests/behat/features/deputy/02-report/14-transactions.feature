Feature: deputy / report / account transactions

    @deputy
    Scenario: money in 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-accounts"
        And I follow "account-moneyin"
        # check no data was previously saved 
        Then the following fields should have the corresponding values:
            | transactions_transactionsIn_0_amounts_0        |  |
            | transactions_transactionsIn_26_amounts_0       |  |
            | transactions_transactionsIn_26_moreDetails  |  |
        And I save the page as "report-account-transactions-empty"
        # wrong values (wrong amount types, amount without explanation, explanation without amount)
        When I fill in the following:
            | transactions_transactionsIn_0_amounts_0        | in |
        And I press "transactions_save"
        Then the following fields should have an error:
            | transactions_transactionsIn_0_amounts_0  |
        And I save the page as "report-account-transactions-errors"
        # right values
        When I fill in the following:
            | transactions_transactionsIn_0_amounts_0       | 1250 |
            | transactions_transactionsIn_26_amounts_0      | 2000.0 |
            | transactions_transactionsIn_26_moreDetails | more-details-in-15  |
        And I press "transactions_save"
        Then the form should be valid
        # reload page
        And I follow "account-moneyin"
        # assert value saved
        And the following fields should have the corresponding values:
            | transactions_transactionsIn_0_amounts_0       | 1,250.00 |
            | transactions_transactionsIn_26_amounts_0      | 2,000.00 |
            | transactions_transactionsIn_26_moreDetails | more-details-in-15  |
        And I should see "3,250.00" in the "transaction-total" region
        And I save the page as "report-account-transactions-data-saved"

    @deputy
    Scenario: money out 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open"
        And I follow "edit-accounts"
        And I follow "account-moneyout"
        # check no data was previously saved 
        Then the following fields should have the corresponding values:
            | transactions_transactionsOut_0_amounts_0        |  |
            | transactions_transactionsOut_11_amounts_0       |  |
            | transactions_transactionsOut_11_moreDetails  |  |
        And I save the page as "report-account-transactions-empty"
        # wrong values (wrong amount types, amount without explanation, explanation without amount)
        When I fill in the following:
            | transactions_transactionsOut_0_amounts_0        | in |
        And I press "transactions_save"
        Then the following fields should have an error:
            | transactions_transactionsOut_0_amounts_0  |
        And I save the page as "report-account-transactions-errors"
        # right values
        When I fill in the following:
            | transactions_transactionsOut_0_amounts_0       | 1250 |
            | transactions_transactionsOut_1_amounts_0       |  |
            | transactions_transactionsOut_2_amounts_0       |  |
            | transactions_transactionsOut_3_amounts_0       |  |
            | transactions_transactionsOut_4_amounts_0       |  |
            | transactions_transactionsOut_11_amounts_0      | 2100.0 |
            | transactions_transactionsOut_11_moreDetails | more-details-in-15  |
        And I press "transactions_save"
        Then the form should be valid
        # reload page
        And I follow "account-moneyout"
        # assert value saved
        And the following fields should have the corresponding values:
            | transactions_transactionsOut_0_amounts_0       | 1,250.00 |
            | transactions_transactionsOut_11_amounts_0      | 2,100.00 |
            | transactions_transactionsOut_11_moreDetails | more-details-in-15  |
        And I should see "3,350.00" in the "transaction-total" region
        And I save the page as "report-account-transactions-data-saved"