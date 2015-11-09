Feature: Safeguarding OPG Report

    @safeguarding @user-report @deputy
    Scenario: Setup the test user
      Given I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
      #Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
      When I create a new "Lay Deputy" user "Wilma" "Smith" with email "behat-safe-userreport@publicguardian.gsi.gov.uk"
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
      Then I save the application status into "safereportuser2"

    @safeguarding @user-report @deputy
    Scenario: Enter a report
        When I load the application status from "safereportuser2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I add the following decisions:
            | description  | clientInvolved | clientInvolvedDetails |
            | 3 beds      | yes           | the client was able to decide at 85% |
            | 2 televisions | yes           | the client said he doesnt want a tv anymore |
        And I add the following contacts:
            | contactName | relationship | explanation                    | address       | address2  | county    | postcode | country |
            | Andy White  | brother      |  no explanation                | 45 Noth Road | Islington  | London    | N2 5JF   | GB      |
            | Fred Smith |  Social Worke  | Advices on benefits available | Town Hall     |Maidenhead | Berkshire | SL1 1RR  | GB |
        And I add the following assets:
            | title        | value       |  description       | valuationDate |
            | Vehicles    | 12000.00    |  Mini cooper       | 10/11/2015 |
            | Property    | 250000.0    | 2 beds flat in HA2 |            |
            | Vehicles    | 13000.00    | Alfa Romeo 156 JTD | 10/11/2015 |
        And I add the following bank account:
            | bank    | HSBC - main account |
            | accountNumber | 8765 |
            | sortCode | 88 | 77 | 66 |
            | openingDate   | 1/1/2014 |
            | openingBalance  | 155.000 |
            | moneyIn_0    | 10000.01 |
            | moneyIn_1    | 200.01 |
            | moneyIn_2    | 300.01 |
            | moneyIn_3    | 400.01 |
            | moneyIn_4    | 500.01 |
            | moneyIn_5    | 600.01 |
            | moneyIn_6    | 700.01 |
            | moneyIn_7    | 800.01 |
            | moneyIn_8    | 900.01 |
            | moneyIn_9    | 1000.01 |
            | moneyIn_10   | 1100.01 |
            | moneyIn_11   | 1,200.01 |
            | moneyIn_12   | 1,300.01 |
            | moneyIn_13   | 1,400.01 |
            | moneyIn_14   | 1,500.01 |
            | moneyIn_15   | 1,600.01 | more-details-in-15 |
            | moneyIn_16   | 1,700.01 | more-details-in-16 |
            | moneyIn_17   | 1,800.01 | more-details-in-17 |
            | moneyIn_18   | 1,800.01 | more-details-in-18 |
            | moneyOut_0   | 100.00 |
            | moneyOut_1   | 200.00 |
            | moneyOut_2   | 300.00 |
            | moneyOut_3   | 400.00 |
            | moneyOut_4   | 500.00 |
            | moneyOut_5   | 600.00 |
            | moneyOut_6   | 700.00 |
            | moneyOut_7   | 800.00 |
            | moneyOut_8   | 900.00 |
            | moneyOut_9   | 1000.00 |
            | moneyOut_10  | 1100.00 |
            | moneyOut_11  | 1,200.00 | more-details-out-11 |
            | moneyOut_12  | 1,300.00 | more-details-out-12 |
            | moneyOut_13  | 1,400.00 | more-details-out-13 |
            | moneyOut_14  | 1,500.00 | more-details-out-14 |
            | moneyOut_15  | 1,600.00 | more-details-out-15 |
            | moneyOut_16  | 1,700.00 | more-details-out-16 |
            | moneyOut_17  | 1,800.00 | more-details-out-17 |
            | moneyOut_18  | 1,900.00 | more-details-out-18 |
            | moneyOut_19  | 2,000.00 | more-details-out-19 |
            | moneyOut_20  | 2,100.00 | more-details-out-20 |
            | closingDate    | 1 /1/2015 |
            | closingBalance | 5855.19 |
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        Then I save the application status into "safeguardingreadytosubmit2"
        And I follow "overview-button"
        Then I follow "edit-report_add_further_info"
        Then the URL should match "/report/\d+/add_further_information"
        And I fill in the following:
            | report_add_info_furtherInformation | More info. |
        And I press "report_add_info_saveAndContinue"
        Then the URL should match "/report/\d+/declaration"
        Then I check "report_declaration_agree"
        When I fill in the following:
            | report_declaration_allAgreed_0 | 1 |
        And I press "report_declaration_save"
        And the URL should match "/report/\d+/submitted"
        Then I save the application status into "safereportsubmitted2"

    @safeguarding @user-report @deputy
    Scenario: Report contains a safeguarding section
        When I load the application status from "safereportsubmitted2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the users latest report
        And I should see "Safeguarding"

    @safeguarding @user-report @deputy
    Scenario: When I live with the client dont show further answers
        When I load the application status from "safereportsubmitted2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the users latest report
        Then the "Do you live with the client?" question should be answered with "Yes"
        And I should not see "How often do you or other deputies visit the client?" text
        And I should not see "How often do you or other deputies phone or vieo call the client?" text
        And I should not see "How often do you or other deputies write emails or letters to the client?" text
        And I should not see "How often does the client see other people?" text
        And I should not see "Is there anything else you want to tell us? (optional)" in the "safeguarding" section
        And I save the page as "safeguarding-userreport-live-with-client"

    @safeguarding @user-report @deputy
    Scenario: When dont live with the client, all visists are every day
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
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
        And I submit the report with further info "More info."
        And I view the users latest report
        Then the "Do you live with the client?" question should be answered with "No"
        Then the "How often do you or other deputies visit the client?" question should be answered with "Every day"
        Then the "How often do you or other deputies phone or video call the client?" question should be answered with "Every day"
        Then the "How often do you or other deputies write emails or letters to the client?" question should be answered with "Every day"
        Then the "How often does the client see other people?" question should be answered with "Every day"
        And I save the page as "safeguarding-userreport-doesnt-live-with-client"

    @safeguarding @user-report @deputy
    Scenario: When dont live with the client, all visists are once a week
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | once_a_week |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | once_a_week |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | once_a_week |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | once_a_week |
            | safeguarding_anythingElseToTell | nothing to report |
        And I submit the report with further info "More info."
        And I view the users latest report
        Then the "How often do you or other deputies visit the client?" question should be answered with "at least once a week"
        Then the "How often do you or other deputies phone or video call the client?" question should be answered with "at least once a week"
        Then the "How often do you or other deputies write emails or letters to the client?" question should be answered with "at least once a week"
        Then the "How often does the client see other people?" question should be answered with "at least once a week"

    @safeguarding @user-report @deputy
    Scenario: When dont live with the client, all visists are once a month
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | once_a_month |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | once_a_month |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | once_a_month |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | once_a_month |
            | safeguarding_anythingElseToTell | nothing to report |
        And I submit the report with further info "More info."
        And I view the users latest report
        Then the "How often do you or other deputies visit the client?" question should be answered with "at least once a month"
        Then the "How often do you or other deputies phone or video call the client?" question should be answered with "at least once a month"
        Then the "How often do you or other deputies write emails or letters to the client?" question should be answered with "at least once a month"
        Then the "How often does the client see other people?" question should be answered with "at least once a month"

    @safeguarding @user-report @deputy
    Scenario: When dont live with the client, all visists are twice a year
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | more_than_twice_a_year |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | more_than_twice_a_year |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | more_than_twice_a_year |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | more_than_twice_a_year |
            | safeguarding_anythingElseToTell | nothing to report |
        And I submit the report with further info "More info."
        And I view the users latest report
        Then the "How often do you or other deputies visit the client?" question should be answered with "more than twice a year"
        Then the "How often do you or other deputies phone or video call the client?" question should be answered with "more than twice a year"
        Then the "How often do you or other deputies write emails or letters to the client?" question should be answered with "more than twice a year"
        Then the "How often does the client see other people?" question should be answered with "more than twice a year"

    @safeguarding @user-report @deputy
    Scenario: When dont live with the client, all visists are once a year
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | once_a_year |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | once_a_year |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | once_a_year |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | once_a_year |
            | safeguarding_anythingElseToTell | nothing to report |
        And I submit the report with further info "More info."
        And I view the users latest report
        Then the "How often do you or other deputies visit the client?" question should be answered with "once a year"
        Then the "How often do you or other deputies phone or video call the client?" question should be answered with "once a year"
        Then the "How often do you or other deputies write emails or letters to the client?" question should be answered with "once a year"
        Then the "How often does the client see other people?" question should be answered with "once a year"

    @safeguarding @user-report @deputy
    Scenario: When dont live with the client, all visists are less than once a year
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | less_than_once_a_year |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | less_than_once_a_year |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | less_than_once_a_year |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | less_than_once_a_year |
            | safeguarding_anythingElseToTell | nothing to report |
        And I submit the report with further info "More info."
        And I view the users latest report
        Then the "How often do you or other deputies visit the client?" question should be answered with "Less than once a year"
        Then the "How often do you or other deputies phone or video call the client?" question should be answered with "Less than once a year"
        Then the "How often do you or other deputies write emails or letters to the client?" question should be answered with "Less than once a year"
        Then the "How often does the client see other people?" question should be answered with "Less than once a year"

    @safeguarding @user-report @deputy
    Scenario: When dont live with the client, provide extra info
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
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
        And I submit the report with further info "More info."
        Then I view the users latest report
        Then the "Is there anything else you want to tell us? (optional)" question, in the "safeguarding" section, should be answered with "Nothing to report"

    @safeguarding @user-report @deputy
    Scenario: When care is not funded, indicate this
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_howIsCareFunded_0 | client_pays_for_all |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        Then I view the users latest report
        Then the "Does the client receive care which is paid for?" question should be answered with "No"
        And I should not see "How is the care funded?" text
        And I save the page as "safeguarding-userreport-care-not-funded"

    @safeguarding @user-report @deputy
    Scenario: When care is funded, and client pays for all
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | yes |
            | safeguarding_howIsCareFunded_0 | client_pays_for_all |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        Then I view the users latest report
        Then the "Does the client receive care which is paid for?" question should be answered with "Yes"
        Then the "How is the care funded?" question should be answered with "Client pays for all their own care"
        And I save the page as "safeguarding-userreport-care-funded"

    @safeguarding @user-report @deputy
    Scenario: When care is funded, and client gets some help
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | yes |
            | safeguarding_howIsCareFunded_0 | client_gets_financial_help |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        Then I view the users latest report
        Then the "Does the client receive care which is paid for?" question should be answered with "Yes"
        Then the "How is the care funded?" question should be answered with "Client gets some financial help (for example, from the local authority NHS)"

    @safeguarding @user-report @deputy
    Scenario: When care is funded, and all funded from someone else
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | yes |
            | safeguarding_howIsCareFunded_0 | all_care_is_paid_by_someone_else |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        Then I view the users latest report
        Then the "Does the client receive care which is paid for?" question should be answered with "Yes"
        Then the "How is the care funded?" question should be answered with "All care is paid for by someone else (for example, by the local authority or NHS)"

    @safeguarding @user-report @deputy
    Scenario: Who is doing the caring?
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whenWasCarePlanLastReviewed_month | 1 |
            | safeguarding_whenWasCarePlanLastReviewed_year | 2015 |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        Then I view the users latest report
        Then the "Who is doing the caring?" question should be answered with "Fred Jones"

    @safeguarding @user-report @deputy
    Scenario: When there is no care plan
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whenWasCarePlanLastReviewed_month | 1 |
            | safeguarding_whenWasCarePlanLastReviewed_year | 2015 |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        Then I view the users latest report
        Then the "Does the client have a care plan?" question should be answered with "No"
        And I should not see "When was the care plan last reviewed?" text
        And I save the page as "safeguarding-userreport-no-care-plan"

    @safeguarding @user-report @deputy
    Scenario: When there is a care plan
        When I load the application status from "safeguardingreadytosubmit2"
        And I am logged in as "behat-safe-userreport@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_0 | yes |
            | safeguarding_whenWasCarePlanLastReviewed_month | 2 |
            | safeguarding_whenWasCarePlanLastReviewed_year | 2015 |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        Then I view the users latest report
        Then the "Does the client have a care plan?" question should be answered with "Yes"
        Then the "When was the care plan last reviewed?" question should be answered with "02/2015"
        And I save the page as "safeguarding-userreport-care-plan"

        
        
