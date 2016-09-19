Feature: Safeguarding Data entry

    @deputy
    Scenario: data entry - Setup the test user
      Given emails are sent from "admin" area
      And I load the application status from "init" 
      And I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
      #Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
      When I create a new "ODR-disabled" "Lay Deputy" user "Wilma" "Smith" with email "behat-safe-entry@publicguardian.gsi.gov.uk"
      And I activate the user with password "Abcd1234"
      And I set the user details to:
          | name | John | Doe | | | |
          | address | 102 Petty France | MOJ | London | SW1H 9AJ | GB |
          | phone | 020 3334 3555 | 020 1234 5678  | | | |
      And I set the client details to:
            | name | Peter | White | | | |
            | caseNumber | 12345ABC | | | | |
            | courtDate | 1 | 1 | 2016 | | |
            | allowedCourtOrderTypes_0 | 2 | | | | |
            | address |  1 South Parade | First Floor  | Nottingham  | NG1 2HT  | GB |
            | phone | 07814000111 |  | | | |
      And I set the report start date to "1/1/2016"
      And I set the report end date to "1/1/2016"
      Then the URL should match "report/\d+/overview"
      Then I am on "/logout"
      Then I save the application status into "safeentryuser"

    @deputy
    Scenario: Lives with client
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I fill in the safeguarding form with the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        Then the form should be valid
        When I follow "overview-button"
        And I follow "edit-safeguarding"
        Then the checkbox "safeguarding_doYouLiveWithClient_0" should be checked

    @deputy
    Scenario: Does not live with client
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I fill in the safeguarding form with the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_howOftenDoYouContactClient | every week |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_doesClientHaveACarePlan_1 | no |
        Then the form should be valid
        When I follow "overview-button"
        And I follow "edit-safeguarding"
        And the "safeguarding_howOftenDoYouContactClient" field should contain "every week"
        And I save the page as "safeguarding-dataentry"

    @deputy
    Scenario: Client does not receive care
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I fill in the safeguarding form with the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        Then the form should be valid
        When I follow "overview-button"
        And I follow "edit-safeguarding"    
        Then the checkbox "safeguarding_doesClientReceivePaidCare_1" should be checked

    @deputy
    Scenario: Client does receive care
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I fill in the safeguarding form with the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_0 | yes |
            | safeguarding_howIsCareFunded_0 | client_pays_for_all |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        Then the form should be valid
        When I follow "overview-button"
        And I follow "edit-safeguarding"
        Then the checkbox "safeguarding_doesClientReceivePaidCare_0" should be checked
        And the checkbox "safeguarding_howIsCareFunded_0" should be checked

    @deputy
    Scenario: User must answer sub questions when receiving care
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I fill in the safeguarding form with the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_0 | yes |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And the following fields should have an error:
            | safeguarding_howIsCareFunded_0 |
            | safeguarding_howIsCareFunded_1 |
            | safeguarding_howIsCareFunded_2 |

    @deputy
    Scenario: Who is doing the caring?
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I fill in the safeguarding form with the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        Then the form should be valid
        When I follow "overview-button"
        And I follow "edit-safeguarding"
        And the "safeguarding_whoIsDoingTheCaring" field should contain "Fred Jones"

    @deputy
    Scenario: Client has care plan
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I fill in the safeguarding form with the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_0 | yes |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_whenWasCarePlanLastReviewed_month | 1 |
            | safeguarding_whenWasCarePlanLastReviewed_year | 2016 |
        Then the form should be valid
        When I follow "overview-button"
        And I follow "edit-safeguarding"
        Then the checkbox "safeguarding_doesClientHaveACarePlan_0" should be checked
        And the "safeguarding_whenWasCarePlanLastReviewed_month" field should contain "01"
        And the "safeguarding_whenWasCarePlanLastReviewed_year" field should contain "2016"

    @deputy
    Scenario: Client does not have care plan
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I fill in the safeguarding form with the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        Then the form should be valid
        When I follow "overview-button"
        And I follow "edit-safeguarding"
        Then the checkbox "safeguarding_doesClientHaveACarePlan_1" should be checked

    @deputy
    Scenario: Client must answer sub questions when there is a care plan
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I fill in the safeguarding form with the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_0 | yes |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And the following fields should have an error:
            | safeguarding_whenWasCarePlanLastReviewed_day |
            | safeguarding_whenWasCarePlanLastReviewed_month |
            | safeguarding_whenWasCarePlanLastReviewed_year |

    @deputy
    Scenario: Deputy must answer top level questions
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "edit-safeguarding"
        And I press "safeguarding_save"
        Then the form should be invalid
        And the following fields should have an error:
            | safeguarding_doYouLiveWithClient_0 |
            | safeguarding_doYouLiveWithClient_1 |
            | safeguarding_doesClientReceivePaidCare_0 |
            | safeguarding_doesClientReceivePaidCare_1 |
            | safeguarding_doesClientHaveACarePlan_0 |
            | safeguarding_doesClientHaveACarePlan_1 |
            | safeguarding_whoIsDoingTheCaring |
