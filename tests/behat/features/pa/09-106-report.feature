Feature: PA Deputy fees and expenses (106)

    Scenario: PA deputy fees
        Given I load the application status from "team-users-complete"
        And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000014" region
        And I click on "edit-pa_fee_expense, start"
        # chose "no records"
        Given the step cannot be submitted without making a selection
        Then the step with the following values CANNOT be submitted:
            | fee_exist_hasFees_1 | no |
        And the step with the following values CAN be submitted:
            | fee_exist_hasFees_1       | no   |
            | fee_exist_reasonForNoFees | Test |
        # summary page check
        Given I click on "pa-dashboard, tab-in-progress"
        And I click on "pa-report-open" in the "client-1000014" region
        And I click on "edit-pa_fee_expense"
        Then each text should be present in the corresponding region:
            | No                          | has-fees          |
            | Please answer this question | paid-for-anything |
        # select there are records (from summary page link)
        Given I click on "edit" in the "has-fees" region
        Then the step with the following values CAN be submitted:
            | fee_exist_hasFees_0 | yes |
        # edit debts
        And the step cannot be submitted without making a selection
        And the step with the following values CANNOT be submitted:
            | fee_fees_0_amount |  |
            | fee_fees_1_amount |  |
            | fee_fees_2_amount |  |
            | fee_fees_3_amount |  |
            | fee_fees_4_amount |  |
            | fee_fees_5_amount |  |
            | fee_fees_6_amount |  |
        And the step with the following values CANNOT be submitted:
            | fee_fees_0_amount       | abc                         |   [ERR]   |
            | fee_fees_1_amount       | 76235746253746253746253746  |   [ERR]   |
            | fee_fees_2_amount       | -1                          |   [ERR]   |
            | fee_fees_3_amount       | abc                         |   [ERR]   |
            | fee_fees_4_amount       | 76235746253746253746253746  |   [ERR]   |
            | fee_fees_5_amount       | 1                           |   [OK]    |
            | fee_fees_5_moreDetails  |                             |   [ERR]   |
            | fee_fees_6_amount       | 2                           |   [OK]    |
            | fee_fees_6_moreDetails  |                             |   [ERR]   |
        And the step with the following values CAN be submitted:
            | fee_fees_0_amount       | 12331.234      |
            | fee_fees_1_amount       |                |
            | fee_fees_2_amount       | 1              |
            | fee_fees_3_amount       | 2              |
            | fee_fees_4_amount       | 0              |
            | fee_fees_5_amount       | 2              |
            | fee_fees_5_moreDetails  | TravelExpense  |
            | fee_fees_6_amount       | 0              |
            | fee_fees_6_moreDetails  | ServiceExpense |
        # check record in summary page
        # summary page check
        Given I click on "pa-dashboard, tab-in-progress"
        And I click on "pa-report-open" in the "client-1000014" region
        And I click on "edit-pa_fee_expense"
        Then each text should be present in the corresponding region:
            | £12,331.23     | fee-work-up-to-and-including-cot-made   |
            | £0.00          | fee-annual-management-fee               |
            | £1.00          | fee-annual-property-management-fee      |
            | £2.00          | fee-preparing-and-lodging-annual-report |
            | £0.00          | fee-completition-of-tax-return          |
            | £2.00          | fee-travel-costs                        |
            | TravelExpense  | fee-travel-costs                        |
            | £0.00          | fee-specialist-service                  |
            |                | fee-specialist-service                  |
        # edit debts again
        When I click on "edit" in the "fees-list" region
        Then the following fields should have the corresponding values:
            | fee_fees_0_amount      | 12,331.23     |
            | fee_fees_1_amount      |               |
            | fee_fees_2_amount      | 1.00          |
            | fee_fees_3_amount      | 2.00          |
            | fee_fees_4_amount      | 0.00          |
            | fee_fees_5_amount      | 2.00          |
            | fee_fees_5_moreDetails | TravelExpense |
            | fee_fees_6_amount      | 0.00          |
            | fee_fees_6_moreDetails |               |
        And the step with the following values CAN be submitted:
            | fee_fees_0_amount       | 1             |
            | fee_fees_1_amount       | 2             |
            | fee_fees_2_amount       | 3             |
            | fee_fees_3_amount       | 4             |
            | fee_fees_4_amount       | 5             |
            | fee_fees_5_amount       | 6             |
            | fee_fees_5_moreDetails  | MoreTravel    |
            | fee_fees_6_amount       | 7             |
            | fee_fees_6_moreDetails  | MoreServices  |
        And each text should be present in the corresponding region:
            | £1.00         | fee-work-up-to-and-including-cot-made   |
            | £2.00         | fee-annual-management-fee               |
            | £3.00         | fee-annual-property-management-fee      |
            | £4.00         | fee-preparing-and-lodging-annual-report |
            | £5.00         | fee-completition-of-tax-return          |
            | £6.00         | fee-travel-costs                        |
            | MoreTravel    | fee-travel-costs                        |
            | £7.00         | fee-specialist-service                  |
            | MoreServices  | fee-specialist-service                  |

    Scenario: PA deputy fees
        Given I load the application status from "team-users-complete"
        And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000014" region
        And I click on "edit-pa_fee_expense, start"
        # chose "no records"
        Given the step cannot be submitted without making a selection
        Then the step with the following values CANNOT be submitted:
            | fee_exist_hasFees_1 | no |
        And the step with the following values CAN be submitted:
            | fee_exist_hasFees_1       | no   |
            | fee_exist_reasonForNoFees | Test |
        # summary page check
        Given I click on "pa-dashboard, tab-in-progress"
        And I click on "pa-report-open" in the "client-1000014" region
        And I click on "edit-pa_fee_expense"
        Then each text should be present in the corresponding region:
            | No                          | has-fees          |
            | Please answer this question | paid-for-anything |
        # select there are records (from summary page link)
        Given I click on "edit" in the "has-fees" region
        Then the step with the following values CAN be submitted:
            | fee_exist_hasFees_0 | yes |
        # edit debts
        And the step cannot be submitted without making a selection
        And the step with the following values CANNOT be submitted:
            | fee_fees_0_amount |  |
            | fee_fees_1_amount |  |
            | fee_fees_2_amount |  |
            | fee_fees_3_amount |  |
            | fee_fees_4_amount |  |
            | fee_fees_5_amount |  |
            | fee_fees_6_amount |  |
        And the step with the following values CANNOT be submitted:
            | fee_fees_0_amount       | abc                         |   [ERR]   |
            | fee_fees_1_amount       | 76235746253746253746253746  |   [ERR]   |
            | fee_fees_2_amount       | -1                          |   [ERR]   |
            | fee_fees_3_amount       | abc                         |   [ERR]   |
            | fee_fees_4_amount       | 76235746253746253746253746  |   [ERR]   |
            | fee_fees_5_amount       | 1                           |   [OK]    |
            | fee_fees_5_moreDetails  |                             |   [ERR]   |
            | fee_fees_6_amount       | 2                           |   [OK]    |
            | fee_fees_6_moreDetails  |                             |   [ERR]   |
        And the step with the following values CAN be submitted:
            | fee_fees_0_amount       | 12331.234      |
            | fee_fees_1_amount       |                |
            | fee_fees_2_amount       | 1              |
            | fee_fees_3_amount       | 2              |
            | fee_fees_4_amount       | 0              |
            | fee_fees_5_amount       | 2              |
            | fee_fees_5_moreDetails  | TravelExpense  |
            | fee_fees_6_amount       | 0              |
            | fee_fees_6_moreDetails  | ServiceExpense |
        # check record in summary page
        # summary page check
        Given I click on "pa-dashboard, tab-in-progress"
        And I click on "pa-report-open" in the "client-1000014" region
        And I click on "edit-pa_fee_expense"
        Then each text should be present in the corresponding region:
            | £12,331.23     | fee-work-up-to-and-including-cot-made   |
            | £0.00          | fee-annual-management-fee               |
            | £1.00          | fee-annual-property-management-fee      |
            | £2.00          | fee-preparing-and-lodging-annual-report |
            | £0.00          | fee-completition-of-tax-return          |
            | £2.00          | fee-travel-costs                        |
            | TravelExpense  | fee-travel-costs                        |
            | £0.00          | fee-specialist-service                  |
            |                | fee-specialist-service                  |
        # edit debts again
        When I click on "edit" in the "fees-list" region
        Then the following fields should have the corresponding values:
            | fee_fees_0_amount      | 12,331.23     |
            | fee_fees_1_amount      |               |
            | fee_fees_2_amount      | 1.00          |
            | fee_fees_3_amount      | 2.00          |
            | fee_fees_4_amount      | 0.00          |
            | fee_fees_5_amount      | 2.00          |
            | fee_fees_5_moreDetails | TravelExpense |
            | fee_fees_6_amount      | 0.00          |
            | fee_fees_6_moreDetails |               |
        And the step with the following values CAN be submitted:
            | fee_fees_0_amount       | 1             |
            | fee_fees_1_amount       | 2             |
            | fee_fees_2_amount       | 3             |
            | fee_fees_3_amount       | 4             |
            | fee_fees_4_amount       | 5             |
            | fee_fees_5_amount       | 6             |
            | fee_fees_5_moreDetails  | MoreTravel    |
            | fee_fees_6_amount       | 7             |
            | fee_fees_6_moreDetails  | MoreServices  |
        And each text should be present in the corresponding region:
            | £1.00         | fee-work-up-to-and-including-cot-made   |
            | £2.00         | fee-annual-management-fee               |
            | £3.00         | fee-annual-property-management-fee      |
            | £4.00         | fee-preparing-and-lodging-annual-report |
            | £5.00         | fee-completition-of-tax-return          |
            | £6.00         | fee-travel-costs                        |
            | MoreTravel    | fee-travel-costs                        |
            | £7.00         | fee-specialist-service                  |
            | MoreServices  | fee-specialist-service                  |