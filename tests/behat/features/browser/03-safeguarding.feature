Feature: Browser - Safeguarding

    @browser
    Scenario: browser - Safeguarding all expanded
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I fill in the safeguarding form with the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_howOftenDoYouContactClient | every week |
            | safeguarding_doesClientReceivePaidCare_0 | yes |
            | safeguarding_howIsCareFunded_0 | client_pays_for_all |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_doesClientHaveACarePlan_0 | yes |
            | safeguarding_whenWasCarePlanLastReviewed_month | 1 |
            | safeguarding_whenWasCarePlanLastReviewed_year | 2016 |
        And I save the page as "safeguarding"
        When I follow "overview-button"
        And I follow "edit-safeguarding"
        And the "safeguarding_howOftenDoYouContactClient" field should contain "every week"
