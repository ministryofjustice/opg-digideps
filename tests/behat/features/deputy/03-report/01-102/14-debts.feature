Feature: Report debts

    @deputy
    Scenario: debts
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports, report-2016, edit-debts, start"
        # chose "no records"
        Given the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | yes_no_hasDebts_1 | no |
        # summary page check
        And each text should be present in the corresponding region:
            | No      | has-debts       |
        # select there are records (from summary page link)
        Given I click on "edit" in the "has-debts" region
        And the step with the following values CAN be submitted:
            | yes_no_hasDebts_0 | yes |
        # edit debts
        And the step cannot be submitted without making a selection
        And the step with the following values CANNOT be submitted:
            | debt_debts_0_amount |  |
            | debt_debts_1_amount |  |
            | debt_debts_2_amount |  |
            | debt_debts_3_amount |  |
        And the step with the following values CANNOT be submitted:
            | debt_debts_0_amount       | abc                         |   [ERR]   |
            | debt_debts_1_amount       | 76235746253746253746253746  |   [ERR]   |
            | debt_debts_2_amount       | -1                          |   [ERR]   |
            | debt_debts_3_amount       | 1                           |   [OK]   |
            | debt_debts_3_moreDetails  |                             |   [ERR]   |
        And the step with the following values CAN be submitted:
            | debt_debts_0_amount | 12331.234 |
            | debt_debts_1_amount |  |
            | debt_debts_2_amount | 1 |
            | debt_debts_3_amount | 2 |
            | debt_debts_3_moreDetails | mr |
        # check record in summary page
        And each text should be present in the corresponding region:
            | £12,331.23    | debt-care-fees |
            | £0.00         | debt-credit-cards |
            | £1.00         | debt-loans |
            | £2.00         | debt-other |
            | mr            | debt-other-more-details |
        # edit debts again
        When I click on "edit" in the "debts-list" region
        Then the following fields should have the corresponding values:
            | debt_debts_0_amount | 12,331.23 |
            | debt_debts_1_amount |  |
            | debt_debts_2_amount | 1.00 |
            | debt_debts_3_amount | 2.00 |
            | debt_debts_3_moreDetails | mr |
        And the step with the following values CAN be submitted:
            | debt_debts_0_amount | 1 |
            | debt_debts_1_amount | 2 |
            | debt_debts_2_amount | 3 |
            | debt_debts_3_amount | 4 |
            | debt_debts_3_moreDetails | 5 mr |
        And each text should be present in the corresponding region:
            | £1.00 | debt-care-fees |
            | £2.00 | debt-credit-cards |
            | £3.00 | debt-loans |
            | £4.00 | debt-other |
            | 5 mr  | debt-other-more-details |
