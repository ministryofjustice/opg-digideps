Feature: deputy / report / actions

    @deputy
    Scenario: provide next year report info (actions)
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-actions"
        # submit empty form
        And I press "action_save"
        Then the following fields should have an error:
            | action_doYouExpectFinancialDecisions_0 |
            | action_doYouExpectFinancialDecisions_1 |
            | action_doYouHaveConcerns_0 |
            | action_doYouHaveConcerns_1 |
        # no details
        When I fill in the following:
            | action_doYouExpectFinancialDecisions_0 | yes |
            | action_doYouExpectFinancialDecisionsDetails |  |
            | action_doYouHaveConcerns_0 | yes |
            | action_doYouHaveConcernsDetails |  |
        And I press "action_save"
        Then the following fields should have an error:
            | action_doYouExpectFinancialDecisionsDetails |
            | action_doYouHaveConcernsDetails |
        # form corrects
        Then I fill in the following:
            | action_doYouExpectFinancialDecisions_1 | no |
            | action_doYouExpectFinancialDecisionsDetails | no |
            | action_doYouHaveConcerns_1 | no |
            | action_doYouHaveConcernsDetails | no |
        And I press "action_save"
        And the form should be valid
