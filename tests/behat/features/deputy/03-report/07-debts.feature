Feature: deputy / report / dets

    @deputy
    Scenario: add debt
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I click on "reports,report-2016-open, edit-assets, debts-tab"
#        And I save the page as "report-debts-empty"
#        # no choice
#        When I press "debt_save"
#        Then the following fields should have an error:
#            | debt_hasDebts_0 |
#            | debt_hasDebts_1 |
#        # no
#        When I fill in the following:
#            | debt_hasDebts_1 | no |
#        And I press "debt_save"
#        Then the form should be valid
#        When I click on "debts-tab"
#        Then the following fields should have the corresponding values:
#            | debt_hasDebts_1 | no |
#        # yes, missing info
#        When I fill in the following:
#            | debt_hasDebts_0 | yes |
#            | debt_debts_0_amount |  |
#            | debt_debts_1_amount |  |
#            | debt_debts_2_amount |  |
#            | debt_debts_3_amount |  |
#        And I press "debt_save"
#        Then the form should be invalid
#        # yes, wrong values
#        When I fill in the following:
#            | debt_hasDebts_0 | yes |
#            | debt_debts_0_amount | abc |
#            | debt_debts_1_amount | 76235746253746253746253746 |
#            | debt_debts_2_amount | -1 |
#            | debt_debts_3_amount | - |
#        And I press "debt_save"
#        Then the following fields should have an error:
#            | debt_debts_0_amount |
#            | debt_debts_1_amount |
#            | debt_debts_2_amount |
#            | debt_debts_3_amount |
#        # yes, more details missing
#        When I fill in the following:
#            | debt_hasDebts_0 | yes |
#            | debt_debts_0_amount |  |
#            | debt_debts_1_amount |  |
#            | debt_debts_2_amount |  |
#            | debt_debts_3_amount | 1 |
#            | debt_debts_3_moreDetails |  |
#        And I press "debt_save"
#        Then the following fields should have an error:
#            | debt_debts_3_moreDetails |
#        # yes, correct
#        When I fill in the following:
#            | debt_hasDebts_0 | yes |
#            | debt_debts_0_amount | 0 |
#            | debt_debts_1_amount | 12331.234 |
#            | debt_debts_2_amount |  |
#            | debt_debts_3_amount | 1 |
#            | debt_debts_3_moreDetails | mr |
#        And I press "debt_save"
#        Then the form should be valid
#        # reload page and check saved values
#        When I click on "debts-tab"
#        Then the following fields should have the corresponding values:
#            | debt_hasDebts_0 | yes |
#            | debt_debts_0_amount | 0.00 |
#            | debt_debts_1_amount | 12,331.23 |
#            | debt_debts_2_amount |  |
#            | debt_debts_3_amount | 1.00 |
#            | debt_debts_3_moreDetails | mr |
