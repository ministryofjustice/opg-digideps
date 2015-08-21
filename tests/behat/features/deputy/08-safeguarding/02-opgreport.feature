Feature: Safeguarding OPG Report
    
    @safeguarding @formatted-report @deputy
    Scenario: Setup the test user
      Given I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
      #Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
      When I create a new "Lay Deputy" user "Wilma" "Smith" with email "behat-safe-report@publicguardian.gsi.gov.uk"
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
      Then I save the application status into "safereportuser"

    @safeguarding @formatted-report @deputy
    Scenario: Enter a report
        When I load the application status from "safereportuser"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-decisions"
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
            | accountNumber | 8 | 7 | 6 | 5 | 
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
        Then I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        Then the form should be valid
        Then I save the application status into "safeguardingreadytosubmit"
        When I check "report_submit_reviewed_n_checked"
        And I press "report_submit_submitReport"
        Then the URL should match "/report/\d+/add_further_information"
        And I fill in the following:
            | report_add_info_furtherInformation | More info. |
        And I press "report_add_info_saveAndContinue"
        Then the URL should match "/report/\d+/declaration"
        Then I check "report_declaration_agree"
        And I press "report_declaration_save"
        And the URL should match "/report/\d+/submitted"
        Then I save the application status into "safereportsubmitted"
        
    @safeguarding @formatted-report @deputy
    Scenario: Report contains a safeguarding section
        When I load the application status from "safereportsubmitted"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        Then I should see "Section 4"
        And I should see "Safeguarding"
    
    @safeguarding @formatted-report @deputy
    Scenario: When I live with the client dont show further answers
        When I load the application status from "safereportsubmitted"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        Then I should see "Do you live with the client?"
        And the report should indicate that the "Yes" checkbox for "Do you live with the client" is checked
        And I should not see the "visits" subsection
    
    @safeguarding @formatted-report @deputy
    Scenario: When dont live with the client, expect to see further answers
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | everyday |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | everyday |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | everyday |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | everyday |
            | safeguarding_anythingElseToTell | nothing to report |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        Then I should see "Do you live with the client?"
        And the report should indicate that the "No" checkbox for "Do you live with the client" is checked
        And I should see a subsection called "safeguarding-visits"
        And I should see a subsection called "safeguarding-visitors"
        And I should see a subsection called "safeguarding-furtherinfo"

    @safeguarding @formatted-report @deputy
    Scenario: When dont live with the client, all visits are every day
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | everyday |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | everyday |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | everyday |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | everyday |
            | safeguarding_anythingElseToTell | nothing to report |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And the report should indicate that the "Every day" checkbox for "Visits" is checked
        And the report should indicate that the "Every day" checkbox for "Phone and video calls" is checked
        And the report should indicate that the "Every day" checkbox for "Letters and emails" is checked
        And the report should indicate that the "Every day" checkbox for "How often does the client see other people" is checked

    @safeguarding @formatted-report @deputy
    Scenario: When dont live with the client, all visits are once a week
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | once_a_week |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | once_a_week |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | once_a_week |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | once_a_week |
            | safeguarding_anythingElseToTell | nothing to report |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And the report should indicate that the "At least once a week" checkbox for "Visits" is checked
        And the report should indicate that the "At least once a week" checkbox for "Phone and video calls" is checked
        And the report should indicate that the "At least once a week" checkbox for "Letters and emails" is checked
        And the report should indicate that the "At least once a week" checkbox for "How often does the client see other people" is checked
        
    @safeguarding @formatted-report @deputy
    Scenario: When dont live with the client, all visits are once a month
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | once_a_month |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | once_a_month |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | once_a_month |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | once_a_month |
            | safeguarding_anythingElseToTell | nothing to report |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And the report should indicate that the "At least once a month" checkbox for "Visits" is checked
        And the report should indicate that the "At least once a month" checkbox for "Phone and video calls" is checked
        And the report should indicate that the "At least once a month" checkbox for "Letters and emails" is checked
        And the report should indicate that the "At least once a month" checkbox for "How often does the client see other people" is checked
        
    @safeguarding @formatted-report @deputy
    Scenario: When dont live with the client, all visits are twice a year
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | more_than_twice_a_year |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | more_than_twice_a_year |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | more_than_twice_a_year |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | more_than_twice_a_year |
            | safeguarding_anythingElseToTell | nothing to report |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And the report should indicate that the "More than twice a year" checkbox for "Visits" is checked
        And the report should indicate that the "More than twice a year" checkbox for "Phone and video calls" is checked
        And the report should indicate that the "More than twice a year" checkbox for "Letters and emails" is checked
        And the report should indicate that the "More than twice a year" checkbox for "How often does the client see other people" is checked

    @safeguarding @formatted-report @deputy
    Scenario: When dont live with the client, all visits are once a year
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | once_a_year |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | once_a_year |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | once_a_year |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | once_a_year |
            | safeguarding_anythingElseToTell | nothing to report |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And the report should indicate that the "Once a year" checkbox for "Visits" is checked
        And the report should indicate that the "Once a year" checkbox for "Phone and video calls" is checked
        And the report should indicate that the "Once a year" checkbox for "Letters and emails" is checked
        And the report should indicate that the "Once a year" checkbox for "How often does the client see other people" is checked
        
    @safeguarding @formatted-report @deputy
    Scenario: When dont live with the client, all visits are less than once a year
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | less_than_once_a_year |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | less_than_once_a_year |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | less_than_once_a_year |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | less_than_once_a_year |
            | safeguarding_anythingElseToTell | nothing to report |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And the report should indicate that the "Less than once a year" checkbox for "Visits" is checked
        And the report should indicate that the "Less than once a year" checkbox for "Phone and video calls" is checked
        And the report should indicate that the "Less than once a year" checkbox for "Letters and emails" is checked
        And the report should indicate that the "Less than once a year" checkbox for "How often does the client see other people" is checked
        
    @safeguarding @formatted-report @deputy
    Scenario: When dont live with the client, provide extra info
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | everyday |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | everyday |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | everyday |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | everyday |
            | safeguarding_anythingElseToTell | nothing to report |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And I should see "Is there anything else you want to tell us?" in "safeguarding" section
        And I should see "nothing to report" in "safeguarding-furtherinfo-field"    

        
    @safeguarding @formatted-report @deputy
    Scenario: When care is not funded, indicate this
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_howIsCareFunded_0 | client_pays_for_all |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And the report should indicate that the "No" checkbox for "Does the client receive care which is paid for" is checked
        And the report should not indicate that the "Client pays for all their own care" checkbox for "How is care funded" is checked

    @safeguarding @formatted-report @deputy
    Scenario: When care is funded, and client pays for all
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | yes |
            | safeguarding_howIsCareFunded_0 | client_pays_for_all |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And the report should indicate that the "Yes" checkbox for "Does the client receive care which is paid for" is checked
        And the report should indicate that the "Client pays for all their own care" checkbox for "How is care funded" is checked

    @safeguarding @formatted-report @deputy
    Scenario: When care is funded, and client gets some help
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | yes |
            | safeguarding_howIsCareFunded_0 | client_gets_financial_help |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And the report should indicate that the "Yes" checkbox for "Does the client receive care which is paid for" is checked
        And the report should indicate that the "Client gets financial help" checkbox for "How is care funded" is checked

    @safeguarding @formatted-report @deputy
    Scenario: When care is funded, and all funded from someone else
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | yes |
            | safeguarding_howIsCareFunded_0 | all_care_is_paid_by_someone_else |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And the report should indicate that the "Yes" checkbox for "Does the client receive care which is paid for" is checked
        And the report should indicate that the "All care is paid by someone else" checkbox for "How is care funded" is checked

    @safeguarding @formatted-report @deputy
    Scenario: When there is no care plan
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whenWasCarePlanLastReviewed_day | 1 |
            | safeguarding_whenWasCarePlanLastReviewed_month | 1 |
            | safeguarding_whenWasCarePlanLastReviewed_year | 2015 |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And the report should indicate that the "There is no care plan" checkbox is checked
        And the "safeguarding-last-review-date" element should be empty
            
    @safeguarding @formatted-report @deputy
    Scenario: When there is acare plan
        When I load the application status from "safeguardingreadytosubmit"
        And I am logged in as "behat-safe-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_0 | yes |
            | safeguarding_whenWasCarePlanLastReviewed_day | 1 |
            | safeguarding_whenWasCarePlanLastReviewed_month | 2 |
            | safeguarding_whenWasCarePlanLastReviewed_year | 2015 |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        And I submit the report with further info "More info."
        Then I view the formatted report
        And the report should not indicate that the "There is no care plan" checkbox is checked
        And I should see "2 / 2015" in "safeguarding-last-review-date"