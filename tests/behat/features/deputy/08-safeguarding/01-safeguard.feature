Feature: Safeguarding Data entry

    @safeguarding @entry @deputy
    Scenario: Setup the test user
      Given I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
      #Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
      When I create a new "Lay Deputy" user "Wilma" "Smith" with email "behat-safe-entry@publicguardian.gsi.gov.uk"
      And I activate the user with password "Abcd1234"
      And I set the user details to:
          | name | John | Doe |
          | address | 102 Petty France | MOJ | London | SW1H 9AJ | GB |
          | phone | 020 3334 3555 | 020 1234 5678  |
      And I set the client details to:
            | name | Peter | White |
            | caseNumber | 123456ABC |
            | courtDate | 1 | 1 | 2014 |
            | allowedCourtOrderTypes_0 | 2 |
            | address |  1 South Parade | First Floor  | Nottingham  | NG1 2HT  | GB |
            | phone | 0123456789  |
      And I set the report end date to "1/1/2015"
      Then the URL should match "report/\d+/overview"
      Then I am on "/logout"
      And I reset the email log
      Then I save the application status into "safeentryuser"

    @safeguarding @entry @deputy
    Scenario: Lives with client
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        Then the checkbox "safeguarding_doYouLiveWithClient_0" should be checked

    @safeguarding @entry @deputy
    Scenario: Does not live with client
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | everyday |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | everyday |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | everyday |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | everyday |
            | safeguarding_anythingElseToTell | nothing to report |
        Then the checkbox "safeguarding_doYouLiveWithClient_1" should be checked
        Then the checkbox "safeguarding_howOftenDoYouVisit_0" should be checked
        Then the checkbox "safeguarding_howOftenDoYouPhoneOrVideoCall_0" should be checked
        Then the checkbox "safeguarding_howOftenDoYouWriteEmailOrLetter_0" should be checked
        Then the checkbox "safeguarding_howOftenDoesClientSeeOtherPeople_0" should be checked
        And the "safeguarding_anythingElseToTell" field should contain "nothing to report"
        And I save the page as "safeguarding-dataentry"

    @safeguarding @entry @deputy
    Scenario: User must answer sub questions when not living with client
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And the following fields should have an error:
            | safeguarding_howOftenDoYouVisit_0 |
            | safeguarding_howOftenDoYouVisit_1 |
            | safeguarding_howOftenDoYouVisit_2 |
            | safeguarding_howOftenDoYouVisit_3 |
            | safeguarding_howOftenDoYouVisit_4 |
            | safeguarding_howOftenDoYouVisit_5 |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_1 |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_2 |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_3 |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_4 |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_5 |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_1 |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_2 |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_3 |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_4 |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_5 |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 |
            | safeguarding_howOftenDoesClientSeeOtherPeople_1 |
            | safeguarding_howOftenDoesClientSeeOtherPeople_2 |
            | safeguarding_howOftenDoesClientSeeOtherPeople_3 |
            | safeguarding_howOftenDoesClientSeeOtherPeople_4 |
            | safeguarding_howOftenDoesClientSeeOtherPeople_5 |

    @safeguarding @entry @deputy
    Scenario: Client does not receive care
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        Then the checkbox "safeguarding_doesClientReceivePaidCare_1" should be checked

    @safeguarding @entry @deputy
    Scenario: Client does receive care
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_0 | yes |
            | safeguarding_howIsCareFunded_0 | client_pays_for_all |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        Then the checkbox "safeguarding_doesClientReceivePaidCare_0" should be checked
        And the checkbox "safeguarding_howIsCareFunded_0" should be checked

    @safeguarding @entry @deputy
    Scenario: User must answer sub questions when receiving care
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_0 | yes |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And the following fields should have an error:
            | safeguarding_howIsCareFunded_0 |
            | safeguarding_howIsCareFunded_1 |
            | safeguarding_howIsCareFunded_2 |

    @safeguarding @entry @deputy
    Scenario: Who is doing the caring?
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And the "safeguarding_whoIsDoingTheCaring" field should contain "Fred Jones"

    @safeguarding @entry @deputy
    Scenario: Client has care plan
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_0 | yes |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_whenWasCarePlanLastReviewed_month | 1 |
            | safeguarding_whenWasCarePlanLastReviewed_year | 2015 |
        Then the checkbox "safeguarding_doesClientHaveACarePlan_0" should be checked
        And the "safeguarding_whenWasCarePlanLastReviewed_month" field should contain "01"
        And the "safeguarding_whenWasCarePlanLastReviewed_year" field should contain "2015"

    @safeguarding @entry @deputy
    Scenario: Client does not have care plan
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        Then the checkbox "safeguarding_doesClientHaveACarePlan_1" should be checked

    @safeguarding @entry @deputy
    Scenario: Client must answer sub questions when there is a care plan
        When I load the application status from "safeentryuser"
        And I am logged in as "behat-safe-entry@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_0 | yes |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And the following fields should have an error:
            | safeguarding_whenWasCarePlanLastReviewed_day |
            | safeguarding_whenWasCarePlanLastReviewed_month |
            | safeguarding_whenWasCarePlanLastReviewed_year |

    @safeguarding @entry @deputy
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
            | safeguarding_whenWasCarePlanLastReviewed_day |
            | safeguarding_whenWasCarePlanLastReviewed_month |
            | safeguarding_whenWasCarePlanLastReviewed_year |
