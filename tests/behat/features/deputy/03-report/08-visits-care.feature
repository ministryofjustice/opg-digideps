Feature: deputy / report / visits and care

    @deputy
    Scenario: visits and care steps
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-visits_care"
        # start
        When I click on "start"
        # step 1 empty
        When I press "visits_care_save"
        Then the following fields should have an error:
            | visits_care_doYouLiveWithClient_0 |
            | visits_care_doYouLiveWithClient_1 |
            | visits_care_howOftenDoYouContactClient |
        # step 1 missing details
        When I fill in the following:
            | visits_care_doYouLiveWithClient_1 | no |
            | visits_care_howOftenDoYouContactClient |  |
        And I press "visits_care_save"
        Then the following fields should have an error:
            | visits_care_howOftenDoYouContactClient |
        # step 1 correct
        When I fill in the following:
            | visits_care_doYouLiveWithClient_1 | no |
            | visits_care_howOftenDoYouContactClient | daily  |
        And I press "visits_care_save"
        Then the form should be valid
        # go back, check content, skip
        When I click on "step-back"
        Then the following fields should have the corresponding values:
            | visits_care_doYouLiveWithClient_1 | no |
            | visits_care_howOftenDoYouContactClient | daily  |
        Then I click on "step-skip"
        # step 2 empty
        When I press "visits_care_save"
        Then the following fields should have an error:
            | visits_care_doesClientReceivePaidCare_0 |
            | visits_care_doesClientReceivePaidCare_1 |
            | visits_care_howIsCareFunded_0 |
            | visits_care_howIsCareFunded_1 |
            | visits_care_howIsCareFunded_2 |
        # step 2 missing details
        When I fill in the following:
            | visits_care_doesClientReceivePaidCare_0 | yes |
        And I press "visits_care_save"
        Then the following fields should have an error:
            | visits_care_howIsCareFunded_0 |         |
            | visits_care_howIsCareFunded_1 |         |
            | visits_care_howIsCareFunded_2 |         |
        # step 2 correct
        When I fill in the following:
            | visits_care_doesClientReceivePaidCare_0 | yes |
            | visits_care_howIsCareFunded_0 | client_pays_for_all |
        And I press "visits_care_save"
        Then the form should be valid
        # go back, check content, skip
        When I click on "step-back"
        Then the following fields should have the corresponding values:
            | visits_care_doesClientReceivePaidCare_0 | yes |
            | visits_care_howIsCareFunded_0 | client_pays_for_all |
        Then I click on "step-skip"
        # step 3 empty
        When I press "visits_care_save"
        Then the following fields should have an error:
            | visits_care_whoIsDoingTheCaring |
        # step 3 correct
        When I fill in the following:
            | visits_care_whoIsDoingTheCaring | the brother |
        And I press "visits_care_save"
        Then the form should be valid
        # go back, check content, skip
        When I click on "step-back"
        Then the following fields should have the corresponding values:
            | visits_care_whoIsDoingTheCaring | the brother |
        Then I click on "step-skip"
        # step 4 empty
        When I press "visits_care_save"
        Then the following fields should have an error:
            | visits_care_doesClientHaveACarePlan_0 |
            | visits_care_doesClientHaveACarePlan_1 |
            | visits_care_whenWasCarePlanLastReviewed_month |
            | visits_care_whenWasCarePlanLastReviewed_year |
        # step 4 missing details
        When I fill in the following:
            | visits_care_doesClientHaveACarePlan_0 | yes |
        And I press "visits_care_save"
        Then the following fields should have an error:
            | visits_care_whenWasCarePlanLastReviewed_month |
            | visits_care_whenWasCarePlanLastReviewed_year |
        # step 4 correct
        When I fill in the following:
            | visits_care_doesClientHaveACarePlan_0 | yes |
            | visits_care_whenWasCarePlanLastReviewed_month | 12 |
            | visits_care_whenWasCarePlanLastReviewed_year | 2015 |
        And I press "visits_care_save"
        Then the form should be valid

    @deputy
    Scenario: visits and care summary page and edit
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-visits_care"
        # Summary overview
        Then each text should be present in the corresponding region:
            | No | live-with-client |
            | daily | how-often-contact-client |
            | Yes | does-client-receive-paid-care |
            | pays for all the care | how-is-care-funded |
            | the brother | who-is-doing-caring |
            | Yes | client-has-care-plan |
            | December 2015 | care-plan-last-reviewed |
        # edit and check back link
        When I click on "live-with-client-edit, step-back"
        # edit
        When I click on "live-with-client-edit"
        And I fill in the following:
            | visits_care_doYouLiveWithClient_0 | yes |
        And I press "visits_care_save"
        # check edited
        Then I should see "Yes" in the "live-with-client" region
        And I should not see the "how-often-contact-client" region
