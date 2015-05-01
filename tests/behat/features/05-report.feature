Feature: report
    
    @deputy
    Scenario: test tabs for "Health & Welfare" report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I save the page as "report-health-welfare-homepage"
        And I am on the first report overview page
        Then I should see a "#tab-overview" element
        And I should see a "#tab-decisions" element
        And I should see a "#tab-contacts" element
        But I should not see a "#tab-accounts" element
        And I should not see a "#tab-assets" element

    @deputy
    Scenario: change report type to "Property and Affairs"
        Given I change the report "1" court order type to "Property and Affairs"

    @deputy
    Scenario: test tabs for "Property and Affairs" report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the first report overview page
        And I save the page as "report-property-affairs-homepage"
        Then I should see a "#tab-contacts" element
        And I should see a "#tab-decisions" element
        And I should see a "#tab-accounts" element
        And I should see a "#tab-assets" element

    @deputy
    Scenario: Check report notification and submission warnings
        # set report due
        Given I set the report 1 due
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the first report overview page
        Then I should see the "tab-contacts-warning" region
        Then I should see the "tab-decisions-warning" region
        Then I should see the "tab-accounts-warning" region
        Then I should see the "tab-assets-warning" region
        # disabled element are not visible from behat
        And I should not see a "report_submit_submitReport" element
        # set back report not to be due
        And I set the report 1 not due

    @deputy
    Scenario: add contact
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the first report overview page
        And I follow "tab-contacts"
        And I save the page as "report-contact-empty"
        # wrong form
        And I press "contact_save"
        And I save the page as "report-contact-add-error"
        Then the following fields should have an error:
            | contact_contactName |
            | contact_relationship |
            | contact_explanation |
            | contact_address |
            | contact_postcode |
        # right values
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
        And I save the page as "report-contact-list"
        Then the response status code should be 200
        And the form should not contain an error
        And the URL should match "/report/\d+/contacts"
        And I should see "Andy White" in the "list-contacts" region


    @deputy
    Scenario: add decision
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the first report overview page
        And I follow "tab-decisions"
        And I save the page as "report-decision-empty"
        # form errors
        When I press "decision_save"
        And I save the page as "report-decision-add-error"
        Then the following fields should have an error:
            | decision_description |
            | decision_clientInvolvedDetails |
            | decision_decisionDate_day |
            | decision_decisionDate_month |
            | decision_decisionDate_year |
            | decision_clientInvolvedBoolean_0 |
            | decision_clientInvolvedBoolean_1 |
        # wrong date
        And I fill in the following:
            | decision_description | 2 beds |
            | decision_decisionDate_day | 30 |
            | decision_decisionDate_month | 02 |
            | decision_decisionDate_year | 9999 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I press "decision_save"
        And the form should contain an error
        # date not in report range
        And I fill in the following:
            #| decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 30 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2002 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I press "decision_save"
        And the form should contain an error
        # missing involvement details
        And I fill in the following:
            | decision_description | 2 beds |
            | decision_decisionDate_day | 30 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2015 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails |  |
        And I press "decision_save"
        And the form should contain an error
        # add decision on 1/1/2015
        And I fill in the following:
            | decision_description | 2 beds |
            | decision_decisionDate_day | 1 |
            | decision_decisionDate_month | 1 |
            | decision_decisionDate_year | 2015 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I press "decision_save"
        And I save the page as "report-decision-list"
        Then the response status code should be 200
        And the form should not contain an error
        When I click on "add-a-decision"
        # add another decision on 31/12/2015
         And I fill in the following:
            #| decision_title | Sold house in Sw18 |
            | decision_description | 3 beds |
            | decision_decisionDate_day | 2 |
            | decision_decisionDate_month | 1 |
            | decision_decisionDate_year | 2015 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 85% |
        And I press "decision_save"
        And the form should not contain an error
        And I should see "2 beds" in the "list-decisions" region
        And I should see "3 beds" in the "list-decisions" region
        
    @deputy
    Scenario: add asset
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the first report overview page
        And I follow "tab-assets"
        And I save the page as "report-assets-empty"
        # wrong form
        And I press "asset_save"
        And I save the page as "report-assets-add-error-empty"
        Then the following fields should have an error:
            | asset_title |
            | asset_value |
            | asset_description |
        # invalid date
        When I fill in the following:
            | asset_title       | Vehicles | 
            | asset_value       | 13000.00 | 
            | asset_description | Alfa Romeo 156 1.9 JTD | 
            | asset_valuationDate_day | 99 | 
            | asset_valuationDate_month |  | 
            | asset_valuationDate_year | 2015 | 
        And I press "asset_save"
        And I save the page as "report-assets-add-error-date"
        Then the following fields should have an error:
            | asset_valuationDate_day |
            | asset_valuationDate_month |
            | asset_valuationDate_year |
        # first asset (empty date)
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


    @deputy
    Scenario: add account
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the first report overview page
        And I follow "tab-accounts"
        And I save the page as "report-account-empty"
        # empty form
        And I press "account_save"
        And I save the page as "report-account-add-error"
        Then the following fields should have an error:
            | account_bank |
            | account_accountNumber_part_1 |
            | account_accountNumber_part_2 |
            | account_accountNumber_part_3 |
            | account_accountNumber_part_4 |
            | account_sortCode_sort_code_part_1 |
            | account_sortCode_sort_code_part_2 |
            | account_sortCode_sort_code_part_3 |
            | account_openingDate_day |
            | account_openingDate_month |
            | account_openingDate_year |
            | account_openingBalance |
        # test validators
        When I fill in the following:
            | account_bank    | HSBC - main account | 
            # invalid number
            | account_accountNumber_part_1 | a | 
            | account_accountNumber_part_2 | b | 
            | account_accountNumber_part_3 | c | 
            | account_accountNumber_part_4 | d | 
            # invalid sort code
            | account_sortCode_sort_code_part_1 | g |
            | account_sortCode_sort_code_part_2 | h |
            | account_sortCode_sort_code_part_3 |  |
            # date outside report range
            | account_openingDate_day   | 5 |
            | account_openingDate_month | 4 |
            | account_openingDate_year  | 1983 |
            | account_openingBalance  | 1,155.00 |
        And I press "account_save"
        Then the following fields should have an error:
            | account_accountNumber_part_1 |
            | account_accountNumber_part_2 |
            | account_accountNumber_part_3 |
            | account_accountNumber_part_4 |
            | account_sortCode_sort_code_part_1 |
            | account_sortCode_sort_code_part_2 |
            | account_sortCode_sort_code_part_3 |
            | account_openingDate_day |
            | account_openingDate_month |
            | account_openingDate_year |
        # right values
        And I fill in the following:
            | account_bank    | HSBC - main account | 
            | account_accountNumber_part_1 | 8 | 
            | account_accountNumber_part_2 | 7 | 
            | account_accountNumber_part_3 | 6 | 
            | account_accountNumber_part_4 | 5 | 
            | account_sortCode_sort_code_part_1 | 88 |
            | account_sortCode_sort_code_part_2 | 77 |
            | account_sortCode_sort_code_part_3 | 66 |
            | account_openingDate_day   | 5 |
            | account_openingDate_month | 4 |
            | account_openingDate_year  | 2015 |
            | account_openingBalance  | 1,155.00 |
        And I press "account_save"
        And I save the page as "report-account-list"
        Then the response status code should be 200
        And the form should not contain an error
        And the URL should match "/report/\d+/account/\d+"
        When I follow "tab-accounts"
        And I should see "HSBC - main account" in the "list-accounts" region
        And I should see "8765" in the "list-accounts" region
        And I should see "£1,155.00" in the "list-accounts" region
        
    @deputy
    Scenario: edit account
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the account "8765" page of the first report
        And I click on "edit-account-details"
        And I save the page as "report-account-edit-start"
        # assert fields are filled in from db correctly
        Then the following fields should have the corresponding values:
            | account_bank    | HSBC - main account | 
            | account_accountNumber_part_1 | 8 | 
            | account_accountNumber_part_2 | 7 | 
            | account_accountNumber_part_3 | 6 | 
            | account_accountNumber_part_4 | 5 | 
            | account_sortCode_sort_code_part_1 | 88 |
            | account_sortCode_sort_code_part_2 | 77 |
            | account_sortCode_sort_code_part_3 | 66 |
            | account_openingDate_day   | 5 |
            | account_openingDate_month | 4 |
            | account_openingDate_year  | 2015 |
            | account_openingBalance  | 1,155.00 |
        # check invalid values
        When I fill in the following:
            | account_bank    |  | 
            | account_accountNumber_part_1 | a | 
            | account_accountNumber_part_2 | 123 | 
            | account_accountNumber_part_3 | - | 
            | account_accountNumber_part_4 |  | 
            | account_sortCode_sort_code_part_1 | a |
            | account_sortCode_sort_code_part_2 | 123 |
            | account_sortCode_sort_code_part_3 |  |
            | account_openingDate_day   |  |
            | account_openingDate_month | 13 |
            | account_openingDate_year  | string |
            | account_openingBalance  |  |
        And I press "account_save"
        Then the following fields should have an error:
            | account_bank |
            | account_accountNumber_part_1 |
            | account_accountNumber_part_2 |
            | account_accountNumber_part_3 |
            | account_accountNumber_part_4 |
            | account_sortCode_sort_code_part_1 |
            | account_sortCode_sort_code_part_2 |
            | account_sortCode_sort_code_part_3 |
            | account_openingDate_day |
            | account_openingDate_month |
            | account_openingDate_year |
            | account_openingBalance |
        And I save the page as "report-account-edit-errors"
        # right values
        When I fill in the following:
            | account_bank    | HSBC main account | 
            | account_accountNumber_part_1 | 1 | 
            | account_accountNumber_part_2 | 2 | 
            | account_accountNumber_part_3 | 3 | 
            | account_accountNumber_part_4 | 4 | 
            | account_sortCode_sort_code_part_1 | 12 |
            | account_sortCode_sort_code_part_2 | 34 |
            | account_sortCode_sort_code_part_3 | 56 |
            | account_openingDate_day   | 1 |
            | account_openingDate_month | 1 |
            | account_openingDate_year  | 2015 |
            | account_openingBalance  | 1,150.00 |
        And I press "account_save"
        # check values are saved
        When I click on "edit-account-details"
        Then the following fields should have the corresponding values:
            | account_bank    | HSBC main account | 
            | account_accountNumber_part_1 | 1 | 
            | account_accountNumber_part_2 | 2 | 
            | account_accountNumber_part_3 | 3 | 
            | account_accountNumber_part_4 | 4 | 
            | account_sortCode_sort_code_part_1 | 12 |
            | account_sortCode_sort_code_part_2 | 34 |
            | account_sortCode_sort_code_part_3 | 56 |
            | account_openingDate_day   | 1 |
            | account_openingDate_month | 1 |
            | account_openingDate_year  | 2015 |
            | account_openingBalance  | 1,150.00 | 
        And I save the page as "report-account-edit-reloaded"


    @deputy
    Scenario: add another account 6666 (will be deleted by next scenario)
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the accounts page of the first report
        # add another account
        And I fill in the following:
            | account_bank    | Barclays acccount to delete | 
            | account_accountNumber_part_1 | 6 | 
            | account_accountNumber_part_2 | 6 | 
            | account_accountNumber_part_3 | 6 | 
            | account_accountNumber_part_4 | 6 | 
            | account_sortCode_sort_code_part_1 | 55 |
            | account_sortCode_sort_code_part_2 | 55 |
            | account_sortCode_sort_code_part_3 | 55 |
            | account_openingDate_day   | 4 |
            | account_openingDate_month | 4 |
            | account_openingDate_year  | 2015 |
            | account_openingBalance  | 1,300.00 |
        And I press "account_save"


    @deputy
    Scenario: delete account 6666 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the accounts page of the first report
        When I click on "account-6666"
        And I click on "edit-account-details"
        # delete and cancel
        And I click on "delete-account"
        And I click on "delete-confirm-cancel"
        # delete and confirm
        And I click on "delete-account"
        And I press "account_delete"
        Then I should not see the "account-6666" link


    @deputy
    Scenario: add account transactions
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the account "1234" page of the first report
        And I click on "moneyIn-tab"
        And I click on "moneyOut-tab"
        # check no data was previously saved
        Then the following fields should have the corresponding values:
            | transactions_moneyIn_0_amount        |  | 
            | transactions_moneyIn_15_amount       |  | 
            | transactions_moneyIn_15_moreDetails  |  | 
            | transactions_moneyOut_0_amount       |  | 
            | transactions_moneyOut_11_amount      |  | 
            | transactions_moneyOut_11_moreDetails |  | 
        And I save the page as "report-account-transactions-empty"
        # wrong values (wrong amount types and amount without explanation)
        When I fill in the following:
            | transactions_moneyIn_0_amount        | in | 
            | transactions_moneyIn_1_amount        | 25,0000 | 
            | transactions_moneyIn_2_amount        | 25.25.25 | 
            | transactions_moneyIn_3_amount        | 250.250,12 | 
            | transactions_moneyOut_11_amount      | 250.12 | 
            | transactions_moneyOut_11_moreDetails |  | 
        And I press "transactions_saveMoneyIn"
        Then the following fields should have an error:
            | transactions_moneyIn_0_amount  |
            | transactions_moneyIn_1_amount  |
            | transactions_moneyIn_2_amount  |
            | transactions_moneyIn_3_amount  |
            | transactions_moneyOut_11_id |
            | transactions_moneyOut_11_type |
            | transactions_moneyOut_11_amount |
            | transactions_moneyOut_11_moreDetails |
        And I save the page as "report-account-transactions-errors"    
        # right values
        When I fill in the following:
            | transactions_moneyIn_0_amount       | 1,250 | 
            | transactions_moneyIn_1_amount       |  | 
            | transactions_moneyIn_2_amount       |  | 
            | transactions_moneyIn_3_amount       |  | 
            | transactions_moneyIn_15_amount      | 2,000.0 | 
            | transactions_moneyIn_15_moreDetails | more-details-in-15  |
            | transactions_moneyOut_0_amount       | 02500 | 
            | transactions_moneyOut_11_amount      | 5000.501 | 
            | transactions_moneyOut_11_moreDetails | more-details-out-11 | 
        And I press "transactions_saveMoneyIn"
        Then the form should not contain an error
        # assert value saved
        And the following fields should have the corresponding values:
            | transactions_moneyIn_0_amount       | 1,250.00 | 
            | transactions_moneyIn_15_amount      | 2,000.00 | 
            | transactions_moneyIn_15_moreDetails | more-details-in-15  |
            | transactions_moneyOut_0_amount       | 2,500.00 | 
            | transactions_moneyOut_11_amount      | 5,000.50 | 
            | transactions_moneyOut_11_moreDetails | more-details-out-11 | 
        And I should see "£3,250.00" in the "moneyIn-total" region
        And I should see "£7,500.50" in the "moneyOut-total" region
        And I should see "£-3,100.50" in the "money-totals" region
        And I save the page as "report-account-transactions-data-saved"

    @deputy
    Scenario: edit bank account, check edit account does not show closing balance
        Given I set the report 1 not due
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the account "1234" page of the first report
        And I click on "edit-account-details"
        #TODO

    @deputy
    Scenario: add closing balance to account
        Given I set the report 1 not due
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the accounts page of the first report
        Then I should not see the "account-1-add-closing-balance" link
        When I set the report 1 due
        And I am on the accounts page of the first report
        Then I should see the "account-1234-warning" region
        When I click on "account-1234"
        Then the following fields should have the corresponding values:
            | accountBalance_closingDate_day   | | 
            | accountBalance_closingDate_month | | 
            | accountBalance_closingDate_year  | | 
            | accountBalance_closingBalance    | | 
        # wrong values
        When I fill in the following:
            | accountBalance_closingDate_day   | 99 | 
            | accountBalance_closingDate_month | 99 | 
            | accountBalance_closingDate_year  | 1 | 
            | accountBalance_closingBalance    | invalid value | 
        And I press "accountBalance_save"
        Then the following fields should have an error:
            | accountBalance_closingDate_day   |
            | accountBalance_closingDate_month |
            | accountBalance_closingDate_year  |
            | accountBalance_closingBalance    |
        # right values  
        When I fill in the following:
            | accountBalance_closingDate_day   | 30 | 
            | accountBalance_closingDate_month | 12 | 
            | accountBalance_closingDate_year  | 2015 | 
            | accountBalance_closingBalance    | 3,105.50 | 
        And I press "accountBalance_save"
        Then I should not see the "account-closing-balance-form" region
        # assert transactions are not changed due to the form in the same page
        And I should see "£-3,100.50" in the "money-totals" region
        When I follow "tab-accounts"
        Then I should see "3,105.50" in the "account-1-closing-balance" region
        And I should see "30/12/2015" in the "account-1-closing-date" region


    @deputy
      Scenario: edit closing balance
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the account "1234" page of the first report
        And I click on "edit-account-details"
        Then I save the page as "report-account-edit-after-closing"
        Then the following fields should have the corresponding values:
            | account_closingDate_day   | 30 | 
            | account_closingDate_month | 12 | 
            | account_closingDate_year  | 2015 | 
            | account_closingBalance    | 3,105.50 | 
        # wrong values
        When I fill in the following:
            | account_closingDate_day   |  | 
            | account_closingDate_month | 13 | 
            | account_closingDate_year  | string | 
            | account_closingBalance    |  | 
        And I press "account_save"
        Then the following fields should have an error:
            | account_closingDate_day   |
            | account_closingDate_month |
            | account_closingDate_year  |
            | account_closingBalance    |
        And I save the page as "report-account-edit-after-closing-errors"
        # right values
        When I fill in the following:
            | account_closingDate_day   | 31  | 
            | account_closingDate_month | 12 | 
            | account_closingDate_year  | 2015 | 
            | account_closingBalance    | 3,100.00 | 
        And I press "account_save"
        Then the form should not contain any error
        And I should see "31/12/2015" in the "account-closing-balance-date" region
        And I should see "£3,100.00" in the "account-closing-balance" region

    @deputy
    Scenario: submit report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the first report overview page
        # check there are no notifications
        Then I should not see the "tab-contacts-warning" region
        Then I should not see the "tab-decisions-warning" region
        Then I should not see the "tab-accounts-warning" region
        Then I should not see the "tab-assets-warning" region
        # set report due
        Given I set the report 1 due
        And I am on the first report overview page
        Then I should not see a "tab-contact-notification" element
        # submit without ticking
        When I press "report_submit_submitReport"
        Then the following fields should have an error:
            | report_submit_reviewed_n_checked   |
        # tick and submit
        When I check "report_submit_reviewed_n_checked"
        And I press "report_submit_submitReport"
        Then the URL should match "/report/\d+/declaration"
        # test "go back" link
        And I click on "report-preview-go-back"
        And I check "report_submit_reviewed_n_checked"
        And I press "report_submit_submitReport"
        Then the URL should match "/report/\d+/declaration"
        # preview page: submit without ticking "agree"
        When I press "report_declaration_save"
        Then the following fields should have an error:
            | report_declaration_agree |
        # right values  
        When I check "report_declaration_agree"
        And I press "report_declaration_save"
        And the form should not contain an error
        And the report is submitted
        And the URL should match "/report/\d+/overview"
        #And I should not see "Ready to submit"
        

        
        