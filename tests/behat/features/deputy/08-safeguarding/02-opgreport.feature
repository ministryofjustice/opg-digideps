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
        # Start by adding some decisions
        And I add the following decision:
            | description | 3 beds |
            | clientInvolved | yes | the client was able to decide at 85% |
        And I add the following decision:
            | description | 2 televisions |
            | clientInvolved | yes | the client said he doesnt want a tv anymore |
        # Next, 2 contacts
        When I add the following contact:
            | contactName | Andy White |
            | relationship | brother  |
            | explanation | no explanation |
            | address | 45 Noth Road | Islington | London | N2 5JF | GB |
        And I add the following contact:
            | contactName | Fred Smith |
            | relationship | Social Worke  |
            | explanation | Advices on benefits available |
            | address | Town Hall |Maidenhead | Berkshire | SL1 1RR | GB |
        # Assets
        Then I follow "tab-assets"
        And I click on "add-an-asset"
        And I fill in the following:
            | asset_title       | Vehicles | 
            | asset_value       | 12000.00 | 
            | asset_description | Mini cooper | 
            | asset_valuationDate_day | 10 | 
            | asset_valuationDate_month | 11 | 
            | asset_valuationDate_year | 2015 |
        Then I press "asset_save"
        Then I click on "add-an-asset"
        When I fill in the following:
            | asset_title       | Property | 
            | asset_value       | 250000.00 | 
            | asset_description | 2 beds flat in HA2 | 
            | asset_valuationDate_day |  | 
            | asset_valuationDate_month |  | 
            | asset_valuationDate_year |  |
        And I press "asset_save"
        Then I click on "add-an-asset"
        # 2nd asset (with date)
        And I fill in the following:
            | asset_title       | Vehicles | 
            | asset_value       | 13000.00 | 
            | asset_description | Alfa Romeo 156 JTD | 
            | asset_valuationDate_day | 10 | 
            | asset_valuationDate_month | 11 | 
            | asset_valuationDate_year | 2015 |
        Then I press "asset_save"
        Then I save the application status into "reportwithoutmoney"
        # Bank account
        Then I follow "tab-accounts"
        And I fill in the following:
            | account_bank    | HSBC - main account | 
            | account_accountNumber_part_1 | 8 | 
            | account_accountNumber_part_2 | 7 | 
            | account_accountNumber_part_3 | 6 | 
            | account_accountNumber_part_4 | 5 | 
            | account_sortCode_sort_code_part_1 | 88 |
            | account_sortCode_sort_code_part_2 | 77 |
            | account_sortCode_sort_code_part_3 | 66 |
            | account_openingDate_day   | 1 |
            | account_openingDate_month | 1 |
            | account_openingDate_year  | 2014 |
            | account_openingBalance  | 155.00 |
        And I press "account_save"
        And the form should be valid     
        And I click on "account-8765"
        When I fill in the following:
            | transactions_moneyIn_0_amount       | 10000.01 |
            | transactions_moneyIn_1_amount       | 200.01 |
            | transactions_moneyIn_2_amount       | 300.01 |
            | transactions_moneyIn_3_amount       | 400.01 |
            | transactions_moneyIn_4_amount       | 500.01 |
            | transactions_moneyIn_5_amount       | 600.01 |
            | transactions_moneyIn_6_amount       | 700.01 |
            | transactions_moneyIn_7_amount       | 800.01 |
            | transactions_moneyIn_8_amount       | 900.01 |
            | transactions_moneyIn_9_amount       | 1000.01 |
            | transactions_moneyIn_10_amount      | 1100.01 |
            | transactions_moneyIn_11_amount      | 1,200.01 |
            | transactions_moneyIn_12_amount      | 1,300.01 |
            | transactions_moneyIn_13_amount      | 1,400.01 |
            | transactions_moneyIn_14_amount      | 1,500.01 |
            | transactions_moneyIn_15_amount      | 1,600.01 |
            | transactions_moneyIn_16_amount      | 1,700.01 |
            | transactions_moneyIn_17_amount      | 1,800.01 |
            | transactions_moneyIn_18_amount      | 1,800.01 |
            | transactions_moneyIn_15_moreDetails | more-details-in-15 |
            | transactions_moneyIn_16_moreDetails | more-details-in-16 |
            | transactions_moneyIn_17_moreDetails | more-details-in-17 |
            | transactions_moneyIn_18_moreDetails | more-details-in-18 |
        And I save the page as "moneyinentered"
        And I press "transactions_saveMoneyIn"
        And I save the page as "moneyinsaved"
        When I fill in the following:
            | transactions_moneyOut_0_amount       | 100.00 |
            | transactions_moneyOut_1_amount       | 200.00 |
            | transactions_moneyOut_2_amount       | 300.00 |
            | transactions_moneyOut_3_amount       | 400.00 |
            | transactions_moneyOut_4_amount       | 500.00 |
            | transactions_moneyOut_5_amount       | 600.00 |
            | transactions_moneyOut_6_amount       | 700.00 |
            | transactions_moneyOut_7_amount       | 800.00 |
            | transactions_moneyOut_8_amount       | 900.00 |
            | transactions_moneyOut_9_amount       | 1000.00 |
            | transactions_moneyOut_10_amount      | 1100.00 |
            | transactions_moneyOut_11_amount      | 1,200.00 |
            | transactions_moneyOut_12_amount      | 1,300.00 |
            | transactions_moneyOut_13_amount      | 1,400.00 |
            | transactions_moneyOut_14_amount      | 1,500.00 |
            | transactions_moneyOut_15_amount      | 1,600.00 |
            | transactions_moneyOut_16_amount      | 1,700.00 |
            | transactions_moneyOut_17_amount      | 1,800.00 |
            | transactions_moneyOut_18_amount      | 1,900.00 |
            | transactions_moneyOut_19_amount      | 2,000.00 |
            | transactions_moneyOut_20_amount      | 2,100.00 |
            | transactions_moneyOut_11_moreDetails | more-details-out-11 |
            | transactions_moneyOut_12_moreDetails | more-details-out-12 |
            | transactions_moneyOut_13_moreDetails | more-details-out-13 |
            | transactions_moneyOut_14_moreDetails | more-details-out-14 |
            | transactions_moneyOut_15_moreDetails | more-details-out-15 |
            | transactions_moneyOut_16_moreDetails | more-details-out-16 |
            | transactions_moneyOut_17_moreDetails | more-details-out-17 |
            | transactions_moneyOut_18_moreDetails | more-details-out-18 |
            | transactions_moneyOut_19_moreDetails | more-details-out-19 |
            | transactions_moneyOut_20_moreDetails | more-details-out-20 |
        And I save the page as "moneyoutentered"
        And I press "transactions_saveMoneyOut"
        When I fill in the following:
            | accountBalance_closingDate_day   | 1 | 
            | accountBalance_closingDate_month | 1 | 
            | accountBalance_closingDate_year  | 2015 | 
            | accountBalance_closingBalance    | 5855.19 |
        And I press "accountBalance_save"
        And the form should be valid
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
        And the report should indicate that the "Yes" checkbox for "Do you live with the client?" is checked
        And I should not see the "visits" subsection
    
    @safeguarding @formatted-report @deputy @wip
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
        Then I check "report_submit_reviewed_n_checked"
        And I press "report_submit_submitReport"
        Then I fill in the following:
            | report_add_info_furtherInformation | More info. |
        Then I press "report_add_info_saveAndContinue"
        Then I check "report_declaration_agree"
        And I press "report_declaration_save"
        Then I view the formatted report
        Then I should see "Do you live with the client?"
        And the report should indicate that the "No" checkbox for "Do you live with the client?" is checked
        And I should see the "visits" subsection
        And the report should indicate that the "Every day" checkbox for "Visits" is checked
        And the report should indicate that the "Every day" checkbox for "Phone and video calls" is checked
        And the report should indicate that the "Every day" checkbox for "Letters and email" is checked
        And I should see the "visitors" subsection
        And the report should indicate that the "Every day" checkbox for "How often does the client see other people?" is checked
        And I should see "Is there anything else you want to tell us?" in "safeguarding" section
        And I should see "More info." in "safeguarding-moreinfo"
            