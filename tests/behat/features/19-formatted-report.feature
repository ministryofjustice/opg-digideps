Feature: Formatted Report
    
    @formatted-report @deputy @wip
    Scenario: Setup the reporting user
        Given I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
        Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
        When I fill in the following:
            | admin_email | behat-report@publicguardian.gsi.gov.uk | 
            | admin_firstname | Wilma | 
            | admin_lastname | Smith | 
            | admin_roleId | 2 |
        And I click on "save"
        Then I should see "behat-report@publicguardian.gsi.gov.uk" in the "users" region
        Then I should see "Wilma Smith" in the "users" region
        Given I am on "/logout"
        When I open the "/user/activate/" link from the email
        Then the response status code should be 200
        When I fill in the following: 
            | set_password_password_first   | Abcd1234 |
            | set_password_password_second  | Abcd1234 |
        And I press "set_password_save"
        Then the form should be valid
        #Then I should be on "user/details"
        When I fill in the following:
            | user_details_firstname | John |
            | user_details_lastname | Doe |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry | GB |
            | user_details_phoneMain | 020 3334 3555  |
            | user_details_phoneAlternative | 020 1234 5678  |
        And I press "user_details_save"
        Then the form should be valid
        When I fill in the following:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 1 |
            | client_courtDate_month | 1 |
            | client_courtDate_year | 2014 |
            | client_allowedCourtOrderTypes_0 | 2 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I press "client_save"
        Then the form should be valid
        When I fill in the following:
            | report_endDate_day | 1 |
            | report_endDate_month | 1 |
            | report_endDate_year | 2015 |
        And I press "report_save"
        Then the form should be valid
        # assert you are on dashboard
        And the URL should match "report/\d+/overview"
        Then I save the application status into "reportuser"

    @formatted-report @deputy @wip
    Scenario: Enter a report
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-decisions"
        # Start by adding some decisions
        When I click on "add-a-decision"
        And I fill in the following:
            | decision_description | 3 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 85% |
        Then I press "decision_save"
        And the form should be valid
        Then I click on "add-a-decision"
        # add another decision
        And I fill in the following:
            | decision_description | 2 televisions |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client said he doesnt want a tv anymore |
        Then I press "decision_save"
        And the form should be valid
        # Next, some contacts
        Then I follow "tab-contacts"
        And I click on "add-a-contact"
        And I fill in the following:
            | contact_contactName | Andy White |
            | contact_relationship | brother  |
            | contact_explanation | no explanation |
            | contact_address | 45 Noth Road |
            | contact_address2 | Inslington |
            | contact_county | London |
            | contact_postcode | N2 5JF |
            | contact_country | GB |
        And I press "contact_save"
        And the form should be valid
        # And Another
        And I click on "add-a-contact"
        And I fill in the following:
            | contact_contactName | Fred Smith |
            | contact_relationship | Social Worker  |
            | contact_explanation | Advices on benefits available |
            | contact_address | Town Hall |
            | contact_address2 | Maidenhead |
            | contact_county | Berkshire |
            | contact_postcode | SL1 1RR |
            | contact_country | GB |
        And I press "contact_save"
        And the form should be valid
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
        Then I save the application status into "readytosubmit"
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
        Then I save the application status into "reportsubmitted"
                
    @formatted-report @deputy
    Scenario: A report lists decisions
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        Then the response status code should be 200
        And I should see "Deputy report for property and financial decisions"
        And I should see "3 beds" in "decisions-section"
        And I should see "the client was able to decide at 85%" in "decisions-section"
        And I should see "2 televisions" in "decisions-section"
        And I should see "the client said he doesnt want a tv anymore" in "decisions-section"

    @formatted-report @deputy
    Scenario: A report says why no decisions were made
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-decisions"
        Then I fill in the following:
          | reason_for_no_decision_reason | small budget |
        And I press "reason_for_no_decision_saveReason"
        Then the form should be valid
        # Next, some contacts
        Then I follow "tab-contacts"
        And I click on "add-a-contact"
        And I fill in the following:
            | contact_contactName | Andy White |
            | contact_relationship | brother  |
            | contact_explanation | no explanation |
            | contact_address | 45 Noth Road |
            | contact_address2 | Inslington |
            | contact_county | London |
            | contact_postcode | N2 5JF |
            | contact_country | GB |
        And I press "contact_save"
        And the form should be valid
        # And Another
        And I click on "add-a-contact"
        And I fill in the following:
            | contact_contactName | Fred Smith |
            | contact_relationship | Social Worker  |
            | contact_explanation | Advices on benefits available |
            | contact_address | Town Hall |
            | contact_address2 | Maidenhead |
            | contact_county | Berkshire |
            | contact_postcode | SL1 1RR |
            | contact_country | GB |
        And I press "contact_save"
        And the form should be valid
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
        # Finally, Assets
        Then I follow "tab-assets"
        And I click on "add-an-asset"
        When I fill in the following:
            | asset_title       | Property | 
            | asset_value       | 250000.00 | 
            | asset_description | 2 beds flat in HA2 | 
            | asset_valuationDate_day |  | 
            | asset_valuationDate_month |  | 
            | asset_valuationDate_year |  |
        And I press "asset_save"
        And I save the page as "report-assets-list-one"
        Then the response status code should be 200
        And the form should be valid
        And I should see "2 beds flat in HA2" in the "list-assets" region
        And I should see "£250,000.00" in the "list-assets" region
        When I click on "add-an-asset"
        # 2nd asset (with date)
        And I fill in the following:
            | asset_title       | Vehicles | 
            | asset_value       | 13000.00 | 
            | asset_description | Alfa Romeo 156 JTD | 
            | asset_valuationDate_day | 10 | 
            | asset_valuationDate_month | 11 | 
            | asset_valuationDate_year | 2015 |
        And I press "asset_save"
        And I save the page as "report-assets-list-two"
        Then I should see "Alfa Romeo 156 JTD" in the "list-assets" region
        And I should see "£13,000.00" in the "list-assets" region
        #Finally we are ready to submit the report
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
        # Now view the report
        And I view the formatted report
        Then the response status code should be 200
        And I should see "Deputy report for property and financial decisions"
        Then I should see "No decisions made:" in "decisions-section"
        And I should see "small budget" in "decisions-section"

    #Scenario: A report shows contacts
    @formatted-report @deputy
    Scenario: A report lists contacts
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        And I should see "Deputy report for property and financial decisions"
        And I should see "Andy White" in "contacts-section"
        And I should see "Fred Smith" in "contacts-section"

    @formatted-report @deputy
    Scenario: A report describes why there are no contacts
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-decisions"
        # Start by adding some decisions
        When I click on "add-a-decision"
        And I fill in the following:
            | decision_description | 3 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 85% |
        Then I press "decision_save"
        And the form should be valid
        Then I click on "add-a-decision"
        # add another decision
        And I fill in the following:
            | decision_description | 2 televisions |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client said he doesnt want a tv anymore |
        Then I press "decision_save"
        And the form should be valid
        # Next, some contacts
        Then I follow "tab-contacts"
        When I fill in "reason_for_no_contact_reason" with "kept in the book"
        And I press "reason_for_no_contact_saveReason"
        Then the form should be valid
        
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
        # Finally, Assets
        Then I follow "tab-assets"
        And I click on "add-an-asset"
        When I fill in the following:
            | asset_title       | Property | 
            | asset_value       | 250000.00 | 
            | asset_description | 2 beds flat in HA2 | 
            | asset_valuationDate_day |  | 
            | asset_valuationDate_month |  | 
            | asset_valuationDate_year |  |
        And I press "asset_save"
        And I save the page as "report-assets-list-one"
        Then the response status code should be 200
        And the form should be valid
        And I should see "2 beds flat in HA2" in the "list-assets" region
        And I should see "£250,000.00" in the "list-assets" region
        When I click on "add-an-asset"
        # 2nd asset (with date)
        And I fill in the following:
            | asset_title       | Vehicles | 
            | asset_value       | 13000.00 | 
            | asset_description | Alfa Romeo 156 JTD | 
            | asset_valuationDate_day | 10 | 
            | asset_valuationDate_month | 11 | 
            | asset_valuationDate_year | 2015 |
        And I press "asset_save"
        And I save the page as "report-assets-list-two"
        Then I should see "Alfa Romeo 156 JTD" in the "list-assets" region
        And I should see "£13,000.00" in the "list-assets" region
        #Finally we are ready to submit the report
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
        # Now view the report
        And I view the formatted report
        Then the response status code should be 200
        And I should see "Deputy report for property and financial decisions"
        And I should see "kept in the book" in "contacts-section"

    @formatted-report @deputy
    Scenario: A report shows the account name and numbers
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        And I should see "HSBC - main account" in "accounts-section"
        And I should see "Current account" in "accounts-section"
        And I should see "88" in "accounts-section"
        And I should see "77" in "accounts-section"
        And I should see "66" in "accounts-section"
        And I should see "8765" in "accounts-section"    
        
    @formatted-report @deputy @wip
    Scenario: A report lists money paid out for an account
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        Then I should see "Summary of money paid out"
        And I should see "Care fees or local authority charges for care" in "money-out"
        And I should see "£ 100.00" in "money-out"
        And I should see "more-details-out-11" in "money-out"

    @formatted-report @deputy
    Scenario: A report lists money paid in to an account
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        Then I should see "Summary of money paid out"
        And I should see "Care fees or local authority charges for care" in "money-out"
        And I should see "£ 10,000.01" in "money-in"
        And I should see "more-details-in-15" in "money-in"
    
    @formatted-report @deputy
    Scenario: A report lists total money in, out, the different and the actual
        When I load the application status from "reportwithoutmoney"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
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
        # Add values into the money out fields.
        And I click on "account-8765"
        When I fill in the following:
            | transactions_moneyIn_0_amount       | 100.01 |
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
            | transactions_moneyIn_18_amount      | 10,800.01 |
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
            | transactions_moneyOut_20_amount      | 2,200.00 |
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
            | accountBalance_closingBalance    | 4855.19 |
        And I press "accountBalance_save"
        And the form should be valid
        #Finally we are ready to submit the report
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
        # Now view the report
        And I view the formatted report
        Then I should see "Balancing the account"
        And I should see "155.00" in "balancing-opening-balance"
        And I should see "27,900.19" in "balancing-total-in"
        And I should see "28,055.19" in "balancing-sub-total"
        And I should see "23,200.00" in "balancing-total-out"
        And I should see "4,855.19" in "balancing-sub-total-2"
        And I should see "4,855.19" in "balancing-closing-balance"
            
    @formatted-report @deputy
    Scenario: A report explains why the balance doesnt match the statement
        When I load the application status from "reportwithoutmoney"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
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
            | account_openingDateExplanation | earlier transaction made with other account |
        And I press "account_save"
        And the form should be valid
        # Add values into the money out fields.
        And I click on "account-8765"
        When I fill in the following:
            | transactions_moneyIn_0_amount       | 100.01 |
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
            | accountBalance_closingBalance    | 155.00 |
        And I press "accountBalance_save"
        Then the following fields should have an error:
            | accountBalance_closingBalance    |
            | accountBalance_closingBalanceExplanation    |
        Then I fill in the following:
            | accountBalance_closingBalanceExplanation| £ 100.50 moved to other account |
        And I press "accountBalance_save"
        Then the form should be valid
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
        # Now view the report
        And I view the formatted report
        And I should see "£ 100.50 moved to other account" in "accountBalance_closingBalanceExplanation"
        
    @formatted-report @deputy    
    Scenario: A report explains why the opening date is off
        When I load the application status from "reportwithoutmoney"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
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
            | account_openingDate_month | 2 |
            | account_openingDate_year  | 2014 |
            | account_openingBalance  | 155.00 |
            | account_openingDateExplanation | earlier transaction made with other account |
        And I press "account_save"
        And the form should be valid
        # Add values into the money out fields.
        And I click on "account-8765"
        When I fill in the following:
            | transactions_moneyIn_0_amount       | 100.01 |
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
            | accountBalance_closingBalance    | 155.00 |
        And I press "accountBalance_save"
        Then the following fields should have an error:
            | accountBalance_closingBalance    |
            | accountBalance_closingBalanceExplanation    |
        Then I fill in the following:
            | accountBalance_closingBalanceExplanation| £ 100.50 moved to other account |
        And I press "accountBalance_save"
        #Finally we are ready to submit the report
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
        # Now view the report
        And I view the formatted report
        And I should see "earlier transaction made with other account" in "account-date-explanation"

    @formatted-report @deputy    
    Scenario: A report explains why the closing date is off
        When I load the application status from "reportwithoutmoney"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
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
            | account_openingDate_month | 2 |
            | account_openingDate_year  | 2014 |
            | account_openingBalance  | 155.00 |
            | account_openingDateExplanation | open date reason |
        And I press "account_save"
        And the form should be valid
        # Add values into the money out fields.
        And I click on "account-8765"
        When I fill in the following:
            | transactions_moneyIn_0_amount       | 100.01 |
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
            | accountBalance_closingDate_day   | 11 | 
            | accountBalance_closingDate_month | 11 | 
            | accountBalance_closingDate_year  | 2014 | 
            | accountBalance_closingBalance    | 4855.19 |
        And I press "accountBalance_save"
        Then I fill in the following:
            | accountBalance_closingDateExplanation| closing date explanation |
            | accountBalance_closingBalanceExplanation| £ 100.50 moved to other account |
        And I press "accountBalance_save"
        #Finally we are ready to submit the report
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
        # Now view the report
        And I view the formatted report
        And I should see "closing date explanation" in "account-date-explanation"

    @formatted-report @deputy
    Scenario: A report lists asset types in order
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        And I should see "Client’s assets and debts"
        Then the 1 asset group should be "Property"
        And the 2 asset group should be "Vehicles"

    @formatted-report @deputy
    Scenario: A report lists asset details
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        And the 1 asset in the "Vehicles" asset group should have a "description" "Mini cooper"
        And the 1 asset in the "Vehicles" asset group should have a "valuationDate" "10 / 11 / 2015"
        And the 1 asset in the "Vehicles" asset group should have a "value" "£12,000.00"
        
    @formatted-report @deputy
    Scenario: A report shows blank valuation date if there isn't one
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        And the 1 asset in the "Property" asset group should have an empty "valuationDate"

    @formatted-report @deputy @wip
    Scenario: A report says why there are no assets
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        # No Decisions
        And I follow "tab-decisions"
        Then I fill in the following:
          | reason_for_no_decision_reason | small budget |
        And I press "reason_for_no_decision_saveReason"
        Then the form should be valid
        # No Contacts
        Then I follow "tab-contacts"
        When I fill in "reason_for_no_contact_reason" with "kept in the book"
        And I press "reason_for_no_contact_saveReason"
        Then the form should be valid
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
        # Finally, Assets
        Then I follow "tab-assets"
        And I check "report_no_assets_no_assets"
        And I press "report_no_assets_saveNoAsset"
        Then the form should be valid
        #Finally we are ready to submit the report
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
        And I view the formatted report
        Then the response status code should be 200
        And I should see "My client has no assets" in "assets-section" 
        