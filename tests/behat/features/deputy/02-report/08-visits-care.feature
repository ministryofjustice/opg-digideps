Feature: deputy / report / visits and care

    @deputy
    Scenario: provide visits and care info
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-visits_care"
        # empty form
        When I press "visits_care_save"
        Then the following fields should have an error:
            | visits_care_doYouLiveWithClient_0 |
            | visits_care_doYouLiveWithClient_1 |
            | visits_care_doesClientReceivePaidCare_0 |
            | visits_care_doesClientReceivePaidCare_1 |
            | visits_care_whoIsDoingTheCaring |
            | visits_care_doesClientHaveACarePlan_0 |
            | visits_care_doesClientHaveACarePlan_1 |
        # correct form
        Then I fill in the following:
            | visits_care_doYouLiveWithClient_1 | no |
            | visits_care_howOftenDoYouContactClient | daily |
            | visits_care_doesClientReceivePaidCare_1 | no |
            | visits_care_whoIsDoingTheCaring | Fred Jones |
            | visits_care_doesClientHaveACarePlan_1 | no |
        And I press "visits_care_save"
        And the form should be valid
        When I click on "reports,report-2016-open, edit-visits_care"
        Then the following fields should have the corresponding values:
            | visits_care_doYouLiveWithClient_1 | no |
            | visits_care_howOftenDoYouContactClient | daily |
            | visits_care_doesClientReceivePaidCare_1 | no |
            | visits_care_whoIsDoingTheCaring | Fred Jones |
            | visits_care_doesClientHaveACarePlan_1 | no |
