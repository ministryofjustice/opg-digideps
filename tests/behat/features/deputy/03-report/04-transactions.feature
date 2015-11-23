Feature: deputy / report / account transactions

    @deputy
    Scenario: add account transactions
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I am on the account "1234" page of the "2015" report
#        # check no data was previously saved
#        Then the following fields should have the corresponding values:
#            | transactions_moneyIn_0_amount        |  |
#            | transactions_moneyIn_15_amount       |  |
#            | transactions_moneyIn_15_moreDetails  |  |
#            | transactions_moneyOut_0_amount       |  |
#            | transactions_moneyOut_11_amount      |  |
#            | transactions_moneyOut_11_moreDetails |  |
#        And I save the page as "report-account-transactions-empty"
#        # wrong values (wrong amount types, amount without explanation, explanation without amount)
#        When I fill in the following:
#            | transactions_moneyIn_0_amount        | in |
#            | transactions_moneyIn_4_amount        | 10000000001 |
#            | transactions_moneyOut_11_amount      | 250.12 |
#            | transactions_moneyOut_11_moreDetails |  |
#            | transactions_moneyOut_12_amount |  |
#            | transactions_moneyOut_12_moreDetails | more details given without amount  |
#        And I press "transactions_saveMoneyIn"
#        Then the following fields should have an error:
#            | transactions_moneyIn_0_amount  |
#            | transactions_moneyIn_4_amount  |
#            | transactions_moneyOut_11_id |
#            | transactions_moneyOut_11_type |
#            | transactions_moneyOut_11_amount |
#            | transactions_moneyOut_11_moreDetails |
#            | transactions_moneyOut_12_id |
#            | transactions_moneyOut_12_type |
#            | transactions_moneyOut_12_amount |
#            | transactions_moneyOut_12_moreDetails |
#        And I save the page as "report-account-transactions-errors"
#        # right values
#        When I fill in the following:
#            | transactions_moneyIn_0_amount       | 1,250 |
#            | transactions_moneyIn_1_amount       |  |
#            | transactions_moneyIn_2_amount       |  |
#            | transactions_moneyIn_3_amount       |  |
#            | transactions_moneyIn_4_amount       |  |
#            | transactions_moneyIn_15_amount      | 2,000.0 |
#            | transactions_moneyIn_15_moreDetails | more-details-in-15  |
#            | transactions_moneyOut_0_amount       | 02500 |
#            | transactions_moneyOut_11_amount      | 5000.501 |
#            | transactions_moneyOut_11_moreDetails | more-details-out-11 |
#            | transactions_moneyOut_12_amount      |  |
#            | transactions_moneyOut_12_moreDetails |  |
#        And I press "transactions_saveMoneyIn"
#        Then the form should be valid
#        # assert value saved
#        And the following fields should have the corresponding values:
#            | transactions_moneyIn_0_amount       | 1,250.00 |
#            | transactions_moneyIn_15_amount      | 2,000.00 |
#            | transactions_moneyIn_15_moreDetails | more-details-in-15  |
#            | transactions_moneyOut_0_amount       | 2,500.00 |
#            | transactions_moneyOut_11_amount      | 5,000.50 |
#            | transactions_moneyOut_11_moreDetails | more-details-out-11 |
#        And I should see "£3,250.00" in the "moneyIn-total" region
#        And I should see "£7,500.50" in the "moneyOut-total" region
#        And I should see "£-3,100.50" in the "money-totals" region
#        And I save the page as "report-account-transactions-data-saved"