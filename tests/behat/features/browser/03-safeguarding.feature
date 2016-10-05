Feature: Browser - VisitsCare

    @browser
    Scenario: browser - VisitsCare all expanded
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I fill in the visits and care form with the following:
            | visits_care_doYouLiveWithClient_1 | no |
            | visits_care_howOftenDoYouContactClient | every week |
            | visits_care_doesClientReceivePaidCare_0 | yes |
            | visits_care_howIsCareFunded_0 | client_pays_for_all |
            | visits_care_whoIsDoingTheCaring | Fred Jones |
            | visits_care_doesClientHaveACarePlan_0 | yes |
            | visits_care_whenWasCarePlanLastReviewed_month | 1 |
            | visits_care_whenWasCarePlanLastReviewed_year | 2016 |
        And I save the page as "visits-care"
        When I follow "overview-button"
        And I follow "edit-visits_care"
        And the "visits_care_howOftenDoYouContactClient" field should contain "every week"
