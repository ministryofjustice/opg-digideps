Feature: report
    
    @deputy
    Scenario: test tabs for "Health & Welfare" report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I save the page as "report-health-welfare-homepage"
        #And I am on the first report overview page
        Then I should see a "#tab-overview" element
        And I should see a "#tab-decisions" element
        And I should see a "#tab-contacts" element
        But I should not see a "#tab-accounts" element
        And I should not see a "#tab-assets" element

    @deputy
    Scenario: change report type to "Property and Affairs"
        Given I change the report "1" court order type to "Property and Affairs"
        
    @deputy
    Scenario: edit report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I click on "client-home"
        And I click on "edit-report-period-2015-report"
        Then the following fields should have the corresponding values:
            | report_edit_startDate_day | 01 |
            | report_edit_startDate_month | 01 |
            | report_edit_startDate_year | 2015 |
            | report_edit_endDate_day | 31 |
            | report_edit_endDate_month | 12 |
            | report_edit_endDate_year | 2015 |
        # check validations
        When I fill in the following:
            | report_edit_startDate_day | aa |
            | report_edit_startDate_month | bb |
            | report_edit_startDate_year | c |
            | report_edit_endDate_day |  |
            | report_edit_endDate_month |  |
            | report_edit_endDate_year |  |
        And I press "report_edit_save"
        Then the following fields should have an error:
           | report_edit_startDate_day |
            | report_edit_startDate_month |
            | report_edit_startDate_year |
            | report_edit_endDate_day |
            | report_edit_endDate_month |
            | report_edit_endDate_year |
        # valid values
        When I fill in the following:
            | report_edit_startDate_day | 01 |
            | report_edit_startDate_month | 01 |
            | report_edit_startDate_year | 2015 |
            | report_edit_endDate_day | 31 |
            | report_edit_endDate_month | 12 |
            | report_edit_endDate_year | 2015 |    
        And I press "report_edit_save"
        Then the form should be valid
        # check values
        And I click on "edit-report-period-2015-report"
        Then the following fields should have the corresponding values:
            | report_edit_startDate_day | 01 |
            | report_edit_startDate_month | 01 |
            | report_edit_startDate_year | 2015 |
            | report_edit_endDate_day | 31 |
            | report_edit_endDate_month | 12 |
            | report_edit_endDate_year | 2015 |

    @deputy
    Scenario: test tabs for "Property and Affairs" report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the first report overview page
        And I save the page as "report-property-affairs-homepage"
        Then I should see a "#tab-contacts" element
        And I should see a "#tab-decisions" element
        And I should see a "#tab-accounts" element
        And I should see a "#tab-assets" element

    @deputy
    Scenario: Check report notification and submission warnings
        # set report due
        Given I set the report 1 end date to 3 days ago
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the first report overview page
        Then I should see the "tab-contacts-warning" region
        Then I should see the "tab-decisions-warning" region
        Then I should see the "tab-accounts-warning" region
        Then I should see the "tab-assets-warning" region
        # disabled element are not visible from behat
        And I should not see a "report_submit_submitReport" element
        # set back report not to be due
        And I set the report 1 end date to 3 days ahead

    @deputy
    Scenario: add contact
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the first report overview page
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
        Then the "contact_explanation" field is expandable
        And I fill in the following:
            | contact_contactName | Andy White |
            | contact_relationship | GP  |
            | contact_explanation | I owe him money |
            | contact_address | 45 Noth Road |
            | contact_address2 | Inslington |
            | contact_county | London |
            | contact_postcode | N2 5JF |
            | contact_country | GB |
        And I press "contact_save"
        And I save the page as "report-contact-list"
        Then the response status code should be 200
        And the form should be valid
        And the URL should match "/report/\d+/contacts"
        And I should see "Andy White" in the "list-contacts" region


    @deputy
    Scenario: add decision
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the first report overview page
        And I follow "tab-decisions"
        And I save the page as "report-decision-empty"
        # form errors
        When I press "decision_save"
        And I save the page as "report-decision-add-error"
        Then the following fields should have an error:
            | decision_description |
            | decision_clientInvolvedDetails |
            | decision_clientInvolvedBoolean_0 |
            | decision_clientInvolvedBoolean_1 |
        # missing involvement details
        And I fill in the following:
            | decision_description | 2 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails |  |
        And I press "decision_save"
        And the form should be invalid
        # add decision 
        Then the "decision_description" field is expandable
        And the "decision_clientInvolvedDetails" field is expandable
        And I fill in the following:
            | decision_description | 2 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I press "decision_save"
        And I save the page as "report-decision-list"
        Then the response status code should be 200
        And the form should be valid
        When I click on "add-a-decision"
        # add another decision
         And I fill in the following:
            #| decision_title | Sold house in Sw18 |
            | decision_description | 3 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 85% |
        And I press "decision_save"
        And the form should be valid
        And I should see "2 beds" in the "list-decisions" region
        And I should see "3 beds" in the "list-decisions" region
        
    @deputy
    Scenario: add asset
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the first report overview page
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
            | asset_value       | 10000000001 | 
            | asset_description | Alfa Romeo 156 JTD | 
            | asset_valuationDate_day | 99 | 
            | asset_valuationDate_month |  | 
            | asset_valuationDate_year | 2015 | 
        And I press "asset_save"
        And I save the page as "report-assets-add-error-date"
        Then the following fields should have an error:
            | asset_value |
            | asset_valuationDate_day |
            | asset_valuationDate_month |
            | asset_valuationDate_year |
        # first asset (empty date)
        Then the "asset_description" field should be expandable
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


    @deputy
    Scenario: add account
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the first report overview page
        And I follow "tab-accounts"
        And I save the page as "report-account-empty"
        # empty form
        And I press "account_save"
        And I save the page as "report-account-add-error"
        Then the "account_openingDateExplanation" field is expandable
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
            | account_openingDateExplanation |
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
            | account_openingDateExplanation |
        # missing validation for date mismatch and explanation not given
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
        Then the following fields should have an error:
          | account_openingDate_day |
          | account_openingDate_month |
          | account_openingDate_year |
          | account_openingDateExplanation |
        # correct values (opening balance explanation added)
        When I fill in "account_openingDateExplanation" with "earlier transaction made with other account"
        And I press "account_save"
        And I save the page as "report-account-list"
        Then the response status code should be 200
        And the form should be valid
        And the URL should match "/report/\d+/accounts"
        And I should see "HSBC - main account" in the "list-accounts" region
        When I click on "account-8765"
        Then I should see "earlier transaction made with other account" in the "opening-balance-explanation" region
        # refresh page and check values
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
            | account_openingDate_day   | 05 |
            | account_openingDate_month | 04 |
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
            | account_openingDate_month | 2 |
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
            | account_openingDate_day   | 01 |
            | account_openingDate_month | 02 |
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
            | account_openingDateExplanation | just a test |
        And I press "account_save"
        Then the form should be valid
        


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
    Scenario: assert closing balance form is not shown when there are no transactions
        Given I save the application status into "report-no-totals"
        Given I set the report 1 end date to 3 days ago
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the accounts page of the first report
        Then I should not see the "account-closing-balance-form" region
        Then I load the application status from "report-no-totals"
    

    @deputy
    Scenario: add account transactions
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the account "1234" page of the first report
        # check no data was previously saved
        Then the following fields should have the corresponding values:
            | transactions_moneyIn_0_amount        |  | 
            | transactions_moneyIn_15_amount       |  | 
            | transactions_moneyIn_15_moreDetails  |  | 
            | transactions_moneyOut_0_amount       |  | 
            | transactions_moneyOut_11_amount      |  | 
            | transactions_moneyOut_11_moreDetails |  | 
        And I save the page as "report-account-transactions-empty"
        # wrong values (wrong amount types, amount without explanation, explanation without amount)
        When I fill in the following:
            | transactions_moneyIn_0_amount        | in | 
            | transactions_moneyIn_4_amount        | 10000000001 | 
            | transactions_moneyOut_11_amount      | 250.12 | 
            | transactions_moneyOut_11_moreDetails |  | 
            | transactions_moneyOut_12_amount |  | 
            | transactions_moneyOut_12_moreDetails | more details given without amount  | 
        And I press "transactions_saveMoneyIn"
        Then the following fields should have an error:
            | transactions_moneyIn_0_amount  |
            | transactions_moneyIn_4_amount  |
            | transactions_moneyOut_11_id |
            | transactions_moneyOut_11_type |
            | transactions_moneyOut_11_amount |
            | transactions_moneyOut_11_moreDetails |
            | transactions_moneyOut_12_id |
            | transactions_moneyOut_12_type |
            | transactions_moneyOut_12_amount |
            | transactions_moneyOut_12_moreDetails |
        And I save the page as "report-account-transactions-errors"   
        # right values
        When I fill in the following:
            | transactions_moneyIn_0_amount       | 1,250 | 
            | transactions_moneyIn_1_amount       |  | 
            | transactions_moneyIn_2_amount       |  | 
            | transactions_moneyIn_3_amount       |  | 
            | transactions_moneyIn_4_amount       |  | 
            | transactions_moneyIn_15_amount      | 2,000.0 | 
            | transactions_moneyIn_15_moreDetails | more-details-in-15  |
            | transactions_moneyOut_0_amount       | 02500 | 
            | transactions_moneyOut_11_amount      | 5000.501 | 
            | transactions_moneyOut_11_moreDetails | more-details-out-11 | 
            | transactions_moneyOut_12_amount      |  | 
            | transactions_moneyOut_12_moreDetails |  | 
        And I press "transactions_saveMoneyIn"
        Then the form should be valid
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
        Given I set the report 1 end date to 3 days ahead
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the account "1234" page of the first report
        And I click on "edit-account-details"
        #TODO

    @deputy
    Scenario: add closing balance to account
        Given I set the report 1 end date to 3 days ahead
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the accounts page of the first report
        Then I should not see the "account-1-add-closing-balance" link
        When I set the report 1 end date to 3 days ago
        And I am on the accounts page of the first report
        Then I should see the "account-1234-warning" region
        And I save the page as "report-account-closing-balance-overview"
        When I click on "account-1234"
        And I save the page as "report-account-closing-balance-form"
        Then I should not see a "accountBalance_closingDateExplanation" element
        Then I should not see a "accountBalance_closingBalanceExplanation" element
        Then the following fields should have the corresponding values:
            | accountBalance_closingDate_day   | | 
            | accountBalance_closingDate_month | | 
            | accountBalance_closingDate_year  | | 
            | accountBalance_closingBalance    | | 
        # invalid values
        When I fill in the following:
            | accountBalance_closingDate_day   |  | 
            | accountBalance_closingDate_month |  | 
            | accountBalance_closingDate_year  |  | 
            | accountBalance_closingBalance    | invalid value | 
        And I press "accountBalance_save"
        Then the following fields should have an error:
            | accountBalance_closingDate_day   |
            | accountBalance_closingDate_month |
            | accountBalance_closingDate_year  |
            | accountBalance_closingBalance    |
        And I save the page as "report-account-closing-balance-form-errors"
        # invalid values
        When I fill in the following:
            | accountBalance_closingDate_day   | 99 | 
            | accountBalance_closingDate_month | 99 | 
            | accountBalance_closingDate_year  | 1 | 
            | accountBalance_closingBalance    | 10000000001 | 
        And I press "accountBalance_save"
        Then the following fields should have an error:
            | accountBalance_closingDate_day   |
            | accountBalance_closingDate_month |
            | accountBalance_closingDate_year  |
            | accountBalance_closingBalance    |
            | accountBalance_closingBalanceExplanation    |
        # only date mismatch (30 days ago instead of 3 days ago)
        When I fill in "accountBalance_closingDate_day" with the value of "30 days ago, DD"
        And I fill in "accountBalance_closingDate_month" with the value of "30 days ahead, MM"
        And I fill in "accountBalance_closingDate_year" with the value of "30 days ahead, YYYY"
        And I fill in "accountBalance_closingBalance" with "-3100.50"
        And I press "accountBalance_save"
        Then the following fields should have an error:
            | accountBalance_closingDate_day   |
            | accountBalance_closingDate_month |
            | accountBalance_closingDate_year  |
            | accountBalance_closingDateExplanation | 
        And I should not see a "accountBalance_closingBalanceExplanation" element
        # only balance mismatch (3000 instead of -3,100.50)
        When I fill in "accountBalance_closingDate_day" with the value of "3 days ago, DD"
        And I fill in "accountBalance_closingDate_month" with the value of "3 days ago, MM"
        And I fill in "accountBalance_closingDate_year" with the value of "3 days ago, YYYY"
        And I fill in "accountBalance_closingBalance" with "-3000"
        And I press "accountBalance_save"
        Then the following fields should have an error:
            | accountBalance_closingBalance    |
            | accountBalance_closingBalanceExplanation    |
        And I should not see a "accountBalance_closingDateExplanation" element
        # both date and balance mismatch: assert submit fails
        When I fill in "accountBalance_closingDate_day" with the value of "30 days ago, DD"
        And I fill in "accountBalance_closingDate_month" with the value of "30 days ahead, MM"
        And I fill in "accountBalance_closingDate_year" with the value of "30 days ahead, YYYY"
        And I fill in "accountBalance_closingBalance" with "-3000"
        And I press "accountBalance_save"
        Then the "accountBalance_closingDateExplanation" field should be expandable
        Then the "accountBalance_closingDateExplanation" field should be expandable
        And the "accountBalance_closingBalanceExplanation" field should be expandable
        Then the following fields should have an error:
            | accountBalance_closingDate_day   |
            | accountBalance_closingDate_month |
            | accountBalance_closingDate_year  |
            | accountBalance_closingDateExplanation | 
            | accountBalance_closingBalance    |
            | accountBalance_closingBalanceExplanation    |
        # fix date, assert only balance failes and date explanation disappear
        When I fill in "accountBalance_closingDate_day" with the value of "3 days ago, DD"
        And I fill in "accountBalance_closingDate_month" with the value of "3 days ago, MM"
        And I fill in "accountBalance_closingDate_year" with the value of "3 days ago, YYYY"
        And I press "accountBalance_save"
        Then I should not see a "accountBalance_closingDateExplanation" element
        And the following fields should have an error:
            | accountBalance_closingBalance    |
            | accountBalance_closingBalanceExplanation    |
        # make date invalid, fix balance. assert only date fails and balance explanation disappear
        When I fill in "accountBalance_closingDate_day" with the value of "30 days ago, DD"
        And I fill in "accountBalance_closingDate_month" with the value of "30 days ahead, MM"
        And I fill in "accountBalance_closingDate_year" with the value of "30 days ahead, YYYY"
        And I fill in "accountBalance_closingBalance" with "-3100.50"
        And I press "accountBalance_save"
        Then I should not see a "accountBalance_closingBalanceExplanation" element
        And the following fields should have an error:
            | accountBalance_closingDate_day   |
            | accountBalance_closingDate_month |
            | accountBalance_closingDate_year  |
            | accountBalance_closingDateExplanation | 
        # save with both date and balance mismatch
        # both date and balance mismatch: add explanations
        When I fill in "accountBalance_closingDate_day" with the value of "30 days ahead, DD"
        And I fill in "accountBalance_closingDate_month" with the value of "30 days ahead, MM"
        And I fill in "accountBalance_closingDate_year" with the value of "30 days ahead, YYYY"
        And I fill in "accountBalance_closingBalance" with "-3000"
        And I press "accountBalance_save"
        Then the form should be invalid
        When I fill in the following:
            | accountBalance_closingDateExplanation| not possible to login to homebanking before |
            | accountBalance_closingBalanceExplanation| £ 100.50 moved to other account |
        And I press "accountBalance_save"
        Then the form should be valid
        And I save the page as "report-account-closing-balance-form-valid"
        # assert the form disappeared
        Then I should not see the "account-closing-balance-form" region
        # assert transactions are not changed due to the form in the same page
        And I should see "£-3,100.50" in the "money-totals" region
        And I should see "not possible to login to homebanking before" in the "closing-date-explanation" region
        And I should see "100.50 moved to other account" in the "closing-balance-explanation" region
        # refresh page and check values
        When I follow "tab-accounts"
        Then I should see "3,000.00" in the "account-1-closing-balance" region
        And I should see the value of "30 days ahead, DD/MM/YYYY" in the "account-1-closing-date" region


    @deputy
      Scenario: edit closing balance
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the account "1234" page of the first report
        And I click on "edit-account-details"
        Then I save the page as "report-account-edit-after-closing"
        Then the field "account_closingDate_day" has value of "30 days ahead, DD"
        And the field "account_closingDate_month" has value of "30 days ahead, MM"
        And the field "account_closingDate_year" has value of "30 days ahead, YYYY"
        Then the following fields should have the corresponding values:
            | account_closingDateExplanation  | not possible to login to homebanking before | 
            | account_closingBalance    | -3,000.00 | 
            | account_closingBalanceExplanation | £ 100.50 moved to other account | 
        # invalid values
        When I fill in the following:
            | account_closingDate_day   |  | 
            | account_closingDate_month |  | 
            | account_closingDate_year  |  | 
            | account_closingBalance    |  | 
        And I press "account_save"
        Then the following fields should have an error:
            | account_closingDate_day   |
            | account_closingDate_month |
            | account_closingDate_year  |
            | account_closingBalance    |
        And I should not see the "account_closingDateExplanation" element
        And I should not see the "account_closingBalanceExplanation" element
        And I save the page as "report-account-edit-after-closing-errors"
        # assert explanations disappear when values are ok (bank empties to avoid submissions)
        When go to "/report/1/account/1/edit"
        And I fill in "account_closingDate_day" with the value of "3 days ago, DD"
        And I fill in "account_closingDate_month" with the value of "3 days ahead, MM"
        And I fill in "account_closingDate_year" with the value of "3 days ahead, YYYY"
        And I fill in the following:
            | account_bank | |
            | account_closingBalance    | -3100.50 |
        And I press "account_save"
        Then the following fields should have an error:
            | account_bank   | 
        And I should not see the "account_closingDateExplanation" element
        And I should not see the "account_closingBalanceExplanation" element
        # assert explanations are required
        When go to "/report/1/account/1/edit"
        And I fill in the following:
            | account_closingDateExplanation | |
            | account_closingBalanceExplanation |  | 
        And I press "account_save"
        Then the following fields should have an error:
            | account_closingDate_day   | 
            | account_closingDate_month |
            | account_closingDate_year  |
            | account_closingDateExplanation | 
            | account_closingBalance    | 
            | account_closingBalanceExplanation | 
        # simple save
        When go to "/report/1/account/1/edit"
        And I fill in the following:
            | account_closingDate_day   | 1 |
            | account_closingDate_month | 5 |
            | account_closingDate_year  | 2015 |
            | account_closingDateExplanation | not possible to login to homebanking  |
            | account_closingBalance | -3,000.50 | 
            | account_closingBalanceExplanation | £ 100 moved to other account | 
        And I press "account_save"
        Then the form should be valid
        And I should see "01/05/2015" in the "account-closing-balance-date" region
        And I should see "£-3,000.50" in the "account-closing-balance" region


    @deputy
    Scenario: report further info page
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "client-home"
        Then I should not see the "download-2015-report" link
        When I click on "report-n1"
        # check there are no notifications
        Then I should not see the "tab-contacts-warning" region
        Then I should not see the "tab-decisions-warning" region
        Then I should not see the "tab-accounts-warning" region
        Then I should not see the "tab-assets-warning" region
        # set report due
        Given I set the report 1 end date to 3 days ago
        And I am on the first report overview page
        Then I should not see a "tab-contact-notification" element
        # assert I cannot access the following steps
        Then The URL "/report/1/add_further_information" should not be accessible
        Then The URL "/report/1/add_further_information/edit" should not be accessible
        Then The URL "/report/1/declaration" should not be accessible
        Then The URL "/report/1/submitted" should not be accessible
        # wrong declaration form
        When I go to the first report overview page
        When I press "report_submit_submitReport"
        Then the following fields should have an error:
            | report_submit_reviewed_n_checked   |
        # correct declaration form
        When I am on the first report overview page
        When I confirm the report is ready to be submitted
        Then the URL should match "/report/\d+/add_further_information"
        And I save the page as "report-submit-further-info-empty"
        # test go back link from additional-info page
        When I click on "report-preview-go-back"
        Then the URL should match "/report/\d+/overview"
        # submit without adding anything
        When I confirm the report is ready to be submitted
        And I press "report_add_info_saveAndContinue"
        Then the URL should match "/report/\d+/declaration"
        # add further info, and check they are saved
        When I click on "report-preview-go-back"
        And I confirm the report is ready to be submitted
        And I fill in "report_add_info_furtherInformation" with "    nothing to add     "
        And I press "report_add_info_saveAndContinue"
        # go back and check info was added, and edit them
        And I click on "report-preview-go-back"
        And I confirm the report is ready to be submitted
        Then I should see "nothing to add" in the "additional-info" region
        And I save the page as "report-submit-further-info-view"
        When I click on "edit-information"
        Then the following fields should have the corresponding values:
           | report_add_info_furtherInformation | nothing to add |
        When I fill in "report_add_info_furtherInformation" with "no further info to add"
        And I save the page as "report-submit-further-info-edit"
        And I press "report_add_info_saveAndContinue"
        Then the URL should match "/report/\d+/declaration"
        # test submitting from contacts page
        When I click on "report-preview-go-back"
        And I follow "tab-contacts"
        And I confirm the report is ready to be submitted
        Then the URL should match "/report/\d+/add_further_information"
        And I click on "report-preview-go-back"
        # test submit from decisions page
        When I follow "tab-decisions"
        And I confirm the report is ready to be submitted
        Then the URL should match "/report/\d+/add_further_information"
        And I click on "report-preview-go-back"
        # test submit from accounts page
        When I follow "tab-accounts"
        And I confirm the report is ready to be submitted
        Then the URL should match "/report/\d+/add_further_information"
        And I click on "report-preview-go-back"
        # test submit from account page
        When I am on the account "1234" page of the first report
        And I confirm the report is ready to be submitted
        Then the URL should match "/report/\d+/add_further_information"
        And I click on "report-preview-go-back"
        # test submit from assets page
        When I follow "tab-assets"
        And I confirm the report is ready to be submitted
        Then the URL should match "/report/\d+/add_further_information"

    @deputy
    Scenario: report declaration page
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "client-home"
        Then I should not see the "download-2015-report" link
        When I click on "report-n1"    
        And I confirm the report is ready to be submitted
        And I click on "next"
        # test "go back" link from declaration page
        When I click on "report-preview-go-back"
        Then the URL should match "/report/\d+/overview"
        When I confirm the report is ready to be submitted
        And I click on "next"
        Then the URL should match "/report/\d+/declaration"
        And I save the page as "report-submit-declaration"
        

    @deputy
    Scenario: report submission
        Given I reset the email log
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        # assert after login I'm redirected to report page
        Then the URL should match "/report/\d+/overview"
        # assert I cannot access the sumbmitted page directly
        And the URL "/report/1/submitted" should not be accessible
        # assert I cannot access the submit page from declaration page
        When I go to "/report/1/declaration"
        Then the URL "/report/1/submitted" should not be accessible
        And I go to the first report overview page
        # submit without ticking "agree"
        When I go to "/report/1/declaration"
        And I press "report_declaration_save"
        Then the following fields should have an error:
            | report_declaration_agree |
        # tick agree and submit
        When I check "report_declaration_agree"
        And I press "report_declaration_save"
        And the form should be valid
        And the response status code should be 200
        And the URL should match "/report/\d+/submitted"
        And I save the page as "report-submit-submitted"
        # assert report display page is not broken
        When I go to "/report/1/display"
        Then the response status code should be 200
        And I save the page as "report-submit-display"
        # assert email has been sent/wrote into the disk
        And the last email containing a link matching "/report/[0-9]+/overview" should have been sent to "behat-user@publicguardian.gsi.gov.uk"
        # assert confirmation email has been sent
        And the second_last email should have been sent to "behat-deputyshipservice@publicguardian.gsi.gov.uk"
        
    @deputy
    Scenario: assert 2nd year report has been created
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "client-home"
    And I edit lastest active report
    When I click on "client-home"
    And I click on "report-n1"
    And I save the page as "report-property-affairs-homepage"
    Then I should see a "#tab-contacts" element
    And I should see a "#tab-decisions" element
    And I should see a "#tab-accounts" element
    And I should see a "#tab-assets" element
    When I am on the account "1234" page of the first report
    And I click on "edit-account-details"
    Then the following fields should have the corresponding values:
        | account_bank    | HSBC main account | 
        | account_accountNumber_part_1 | 1 | 
        | account_accountNumber_part_2 | 2 | 
        | account_accountNumber_part_3 | 3 | 
        | account_accountNumber_part_4 | 4 | 
        | account_sortCode_sort_code_part_1 | 12 |
        | account_sortCode_sort_code_part_2 | 34 |
        | account_sortCode_sort_code_part_3 | 56 |
        | account_openingBalance  | -3,000.50 |

    @deputy
    Scenario: assert report is not editable after submission
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I click on "client-home"
        # assert I'm on the client homepage (cannot redirect to report overview as not acessible anymore)
        Then I should be on "/client/show"
        Then I should not see the "edit-report-period-2015-report" link
        And I should not see the "report-n2" link
        And I should see the "report-2015-submitted-on" region
        And the URL "/report/1/overview" should not be accessible
        And the URL "/report/1/contacts" should not be accessible
        And the URL "/report/1/decisions" should not be accessible
        And the URL "/report/1/accounts" should not be accessible
        And the URL "/report/1/account/1" should not be accessible
        And the URL "/report/1/assets" should not be accessible
        
    @deputy
    Scenario: report download
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I click on "client-home"
        # download report from confirmation page
        When I go to "/report/1/submitted"
        When I click on "download-report"
        And the response should contain "123456ABC"
        And the response should contain "Peter White"
        # download report from client page
        #When I go to the homepage
        When I click on "client-home"
        And I click on "download-2015-report"
        And the response should contain "123456ABC"
        And the response should contain "Peter White"
        # test go back link
        When I click on "back-to-client"
        Then I should be on "/client/show"


    @deputy
    Scenario: change report to "not submitted" 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I change the report "1" submitted to "false"
