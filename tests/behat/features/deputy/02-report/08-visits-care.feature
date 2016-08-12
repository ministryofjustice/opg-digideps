Feature: deputy / report / vists and care (old safeguarding)

    @deputy
    Scenario: provide safeguarding info
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-safeguarding"
        # empty form
        When I press "safeguarding_save"
        Then the following fields should have an error:
            | safeguarding_doYouLiveWithClient_0 |
            | safeguarding_doYouLiveWithClient_1 |
            | safeguarding_doesClientReceivePaidCare_0 |
            | safeguarding_doesClientReceivePaidCare_1 |
            | safeguarding_whoIsDoingTheCaring |
            | safeguarding_doesClientHaveACarePlan_0 |
            | safeguarding_doesClientHaveACarePlan_1 |
        # correct form
        Then I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_howOftenDoYouContactClient | daily |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_doesClientHaveACarePlan_1 | no |
        And I press "safeguarding_save"
        And the form should be valid
        When I click on "reports,report-2016-open, edit-safeguarding"
        Then the following fields should have the corresponding values:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_howOftenDoYouContactClient | daily |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_doesClientHaveACarePlan_1 | no |
