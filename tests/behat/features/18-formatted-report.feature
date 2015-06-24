Feature: Formatted Report
    
    @wip @formatted-report
    Scenario: Setup the reporting user
        Given I am on "/login"
        When I fill in the following:
            | login_email     | ADMIN@PUBLICGUARDIAN.GSI.GOV.UK |
            | login_password  | Abcd1234 |
        And I click on "login"
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
        Then the form should not contain an error
        Then I should be on "user/details"
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
        Then the form should not contain an error
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
        Then the form should not contain an error
        When I fill in the following:
            | report_endDate_day | 1 |
            | report_endDate_month | 1 |
            | report_endDate_year | 2015 |
        And I press "report_save"
        Then the form should not contain an error
        # assert you are on dashboard
        And the URL should match "report/\d+/overview"
        Then I save the application status into "reportuser"
        
    @wip
    @formatted-report
    Scenario: A report lists decisions
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I go to "report/1/decisions"
        And I follow "tab-decisions"
        # Start by adding some decisions
        When I click on "add-a-decision"
        And I fill in the following:
            | decision_description | 3 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 85% |
        Then I press "decision_save"
        And the form should not contain an error
        Then I click on "add-a-decision"
        # add another decision
        And I fill in the following:
            | decision_description | 2 televisions |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client said he doesnt want a tv anymore |
        Then I press "decision_save"
        And the form should not contain an error
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
        And the form should not contain an error
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
        And the form should not contain an error
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
        And the form should not contain an error
        When I fill in the following:
            | accountBalance_closingDate_day   | 1 | 
            | accountBalance_closingDate_month | 1 | 
            | accountBalance_closingDate_year  | 2015 | 
            | accountBalance_closingBalance    | 155.00 |
        And I press "accountBalance_save"
        And the form should not contain an error
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
        And the form should not contain an error
        And I should see "2 beds flat in HA2" in the "list-assets" region
        And I should see "£250,000.00" in the "list-assets" region
        When I click on "add-an-asset"
        # 2nd asset (with date)
        And I fill in the following:
            | asset_title       | Vehicles | 
            | asset_value       | 13000.00 | 
            | asset_description | Alfa Romeo 156 1.9 JTD | 
            | asset_valuationDate_day | 10 | 
            | asset_valuationDate_month | 11 | 
            | asset_valuationDate_year | 2015 |
        And I press "asset_save"
        And I save the page as "report-assets-list-two"
        Then I should see "Alfa Romeo 156 1.9 JTD" in the "list-assets" region
        And I should see "£13,000.00" in the "list-assets" region
        #Finally we are ready to submit the report
        When I check "report_submit_reviewed_n_checked"
        And I press "report_submit_submitReport"
        Then the URL should match "/report/\d+/declaration"
        Then I check "report_declaration_agree"
        And I press "report_declaration_save"
        And the URL should match "/report/\d+/submitted"
        # Now view the report
        When I go to "/report/1/formatted"
        Then the response status code should be 200
        And I should see "Deputy report for property and financial decisions"
        And I should see "3 beds" in "decisions-section"
        And I should see "the client was able to decide at 85%" in "decisions-section"
        And I should see "2 televisions" in "decisions-section"
        And I should see "the client said he doesnt want a tv anymore" in "decisions-section"

    @formatted-report    
    Scenario: A report says why no decisions were made
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I go to "report/1/decisions"
        And I follow "tab-decisions"
        Then I fill in the following:
          | reason_for_no_decision_reason | small budget |
        And I press "reason_for_no_decision_saveReason"
        Then the form should not contain an error
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
        And the form should not contain an error
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
        And the form should not contain an error
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
        And the form should not contain an error
        When I fill in the following:
            | accountBalance_closingDate_day   | 1 | 
            | accountBalance_closingDate_month | 1 | 
            | accountBalance_closingDate_year  | 2015 | 
            | accountBalance_closingBalance    | 155.00 |
        And I press "accountBalance_save"
        And the form should not contain an error
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
        And the form should not contain an error
        And I should see "2 beds flat in HA2" in the "list-assets" region
        And I should see "£250,000.00" in the "list-assets" region
        When I click on "add-an-asset"
        # 2nd asset (with date)
        And I fill in the following:
            | asset_title       | Vehicles | 
            | asset_value       | 13000.00 | 
            | asset_description | Alfa Romeo 156 1.9 JTD | 
            | asset_valuationDate_day | 10 | 
            | asset_valuationDate_month | 11 | 
            | asset_valuationDate_year | 2015 |
        And I press "asset_save"
        And I save the page as "report-assets-list-two"
        Then I should see "Alfa Romeo 156 1.9 JTD" in the "list-assets" region
        And I should see "£13,000.00" in the "list-assets" region
        #Finally we are ready to submit the report
        When I check "report_submit_reviewed_n_checked"
        And I press "report_submit_submitReport"
        Then the URL should match "/report/\d+/declaration"
        Then I check "report_declaration_agree"
        And I press "report_declaration_save"
        And the URL should match "/report/\d+/submitted"
        # Now view the report
        When I go to "/report/1/formatted"
        Then the response status code should be 200
        And I should see "Deputy report for property and financial decisions"
        And I should see "small budget" in "decisions-section"

    #Scenario: A report shows contacts
    @formatted-report
    Scenario: A report lists contacts
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I go to "report/1/decisions"
        And I follow "tab-decisions"
        # Start by adding some decisions
        When I click on "add-a-decision"
        And I fill in the following:
            | decision_description | 3 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 85% |
        Then I press "decision_save"
        And the form should not contain an error
        Then I click on "add-a-decision"
        # add another decision
        And I fill in the following:
            | decision_description | 3 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 85% |
        Then I press "decision_save"
        And the form should not contain an error
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
        And the form should not contain an error
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
        And the form should not contain an error
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
        And the form should not contain an error
        When I fill in the following:
            | accountBalance_closingDate_day   | 1 | 
            | accountBalance_closingDate_month | 1 | 
            | accountBalance_closingDate_year  | 2015 | 
            | accountBalance_closingBalance    | 155.00 |
        And I press "accountBalance_save"
        And the form should not contain an error
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
        And the form should not contain an error
        And I should see "2 beds flat in HA2" in the "list-assets" region
        And I should see "£250,000.00" in the "list-assets" region
        When I click on "add-an-asset"
        # 2nd asset (with date)
        And I fill in the following:
            | asset_title       | Vehicles | 
            | asset_value       | 13000.00 | 
            | asset_description | Alfa Romeo 156 1.9 JTD | 
            | asset_valuationDate_day | 10 | 
            | asset_valuationDate_month | 11 | 
            | asset_valuationDate_year | 2015 |
        And I press "asset_save"
        And I save the page as "report-assets-list-two"
        Then I should see "Alfa Romeo 156 1.9 JTD" in the "list-assets" region
        And I should see "£13,000.00" in the "list-assets" region
        #Finally we are ready to submit the report
        When I check "report_submit_reviewed_n_checked"
        And I press "report_submit_submitReport"
        Then the URL should match "/report/\d+/declaration"
        Then I check "report_declaration_agree"
        And I press "report_declaration_save"
        And the URL should match "/report/\d+/submitted"
        # Now view the report
        When I go to "/report/1/formatted"
        Then the response status code should be 200
        And I should see "Deputy report for property and financial decisions"
        And I should see "Andy White" in "contacts-section"
        And I should see "Fred Smith" in "contacts-section"
            
            
    #Scenario: A report shows no contacts
    #Scenario: A report shows accounts
    #Scenario: A report shows the reason for account date mismatch
    #Scenario: A report shows the reason for balance mismatch
    #Scanario: A report shows assets
    #Scanario: A report shows when no assets