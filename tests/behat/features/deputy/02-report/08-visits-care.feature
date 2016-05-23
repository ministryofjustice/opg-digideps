Feature: deputy / report / vists and care (old safeguarding)

    @deputy
    Scenario: provide safeguarding info
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-safeguarding"
        Then I fill in the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        And the form should be valid