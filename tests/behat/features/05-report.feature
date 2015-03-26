Feature: report
    
    @deputy
    Scenario: add contact
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I follow "tab-contacts"
        And I save the page as "report-contact-empty"
        # wrong form
        And I submit the form
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
        And I submit the form
        And I save the page as "report-contact-list"
        Then the response status code should be 200
        And the form should not contain an error
        And I should be on "/report/1/contacts"
        And I should see "Andy White" in the "list-contacts" region


    @deputy
    Scenario: add decision
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I follow "tab-decisions"
        And I save the page as "report-decision-empty"
        # form errors
        When I submit the form
        And I save the page as "report-decision-add-error"
        Then the following fields should have an error:
            #| decision_title |
            | decision_description |
            | decision_clientInvolvedDetails |
            | decision_decisionDate_day |
            | decision_decisionDate_month |
            | decision_decisionDate_year |
            | decision_clientInvolvedBoolean_0 |
            | decision_clientInvolvedBoolean_1 |
        # wrong date
        And I fill in the following:
            #| decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 30 |
            | decision_decisionDate_month | 02 |
            | decision_decisionDate_year | 9999 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
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
        And I submit the form
        And the form should contain an error
        # missing involvement details
        And I fill in the following:
            #| decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 30 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2015 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails |  |
        And I submit the form
        And the form should contain an error
        # add decision on 1/1/2015
        And I fill in the following:
            #| decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 1 |
            | decision_decisionDate_month | 1 |
            | decision_decisionDate_year | 2015 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
        And I save the page as "report-decision-list"
        Then the response status code should be 200
        And the form should not contain an error
        # add another decision on 31/12/2015
         And I fill in the following:
            #| decision_title | Sold house in Sw18 |
            | decision_description | 3 beds |
            | decision_decisionDate_day | 31 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2015 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 85% |
        And I submit the form
        And the form should not contain an error
        And I should see "2 beds" in the "list-decisions" region
        And I should see "3 beds" in the "list-decisions" region
        

    @deputy
    Scenario: test tabs for "Health & Welfare" report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I save the page as "report-health-welfare-homepage"
        When I am on "/report/1/overview"
        Then I should see a "#tab-contacts" element
        And I should see a "#tab-decisions" element
        But I should not see a "#tab-accounts" element
        And I should not see a "#tab-assets" element


    @deputy
    Scenario: change report type to "Property and Affairs"
        Given I change the report "1" court order type to "Property and Affairs"


    @deputy
    Scenario: test tabs for "Property and Affairs" report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I am on "/report/1/overview"
        And I save the page as "report-property-affairs-homepage"
        Then I should see a "#tab-contacts" element
        And I should see a "#tab-decisions" element
        And I should see a "#tab-accounts" element
        And I should see a "#tab-assets" element


    @deputy
    Scenario: add asset
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I follow "tab-assets"
        And I save the page as "report-assets-empty"
        # wrong form
        And I submit the form
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
        And I submit the form
        And I save the page as "report-assets-add-error-date"
        Then the following fields should have an error:
            | asset_valuationDate_day |
            | asset_valuationDate_month |
            | asset_valuationDate_year |
        # 1st asset (empty date)
        When I fill in the following:
            | asset_title       | Property | 
            | asset_value       | 250000.00 | 
            | asset_description | 2 beds flat in HA2 | 
            | asset_valuationDate_day |  | 
            | asset_valuationDate_month |  | 
            | asset_valuationDate_year |  | 
        And I submit the form
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
        And I submit the form
        And I save the page as "report-assets-list-two"
        Then I should see "Alfa Romeo 156 1.9 JTD" in the "list-assets" region
        And I should see "£13,000.00" in the "list-assets" region


    @deputy
    Scenario: add account
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I go to "/report/1/accounts/add"
        And I save the page as "report-account-empty"
        # wrong form
        And I submit the form
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
        # right values
        And I fill in the following:
            | account_bank    | HSBC main account | 
            | account_accountNumber_part_1 | 1 | 
            | account_accountNumber_part_2 | 2 | 
            | account_accountNumber_part_3 | 3 | 
            | account_accountNumber_part_4 | 4 | 
            | account_sortCode_sort_code_part_1 | 12 |
            | account_sortCode_sort_code_part_2 | 34 |
            | account_sortCode_sort_code_part_3 | 56 |
            | account_openingDate_day   | 01 |
            | account_openingDate_month | 01 |
            | account_openingDate_year  | 2015 |
            | account_openingBalance  | 50.00 |
        And I submit the form
        And I save the page as "report-account-list"
        Then the response status code should be 200
        And the form should not contain an error
        And I should be on "/report/1/accounts"
        And I should see "HSBC main account" in the "list-accounts" region
        And I should see "1234" in the "list-accounts" region
    

    @deputy
    Scenario: add account transactions
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I follow "tab-accounts"
        And I click on "account-n1"
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
            | transactions_moneyOut_11_amount      | 250 | 
            | transactions_moneyOut_11_moreDetails |  | 
        And I press "transactions_saveMoneyIn"
        Then the following fields should have an error:
            | transactions_moneyIn_0_amount  |
            | transactions_moneyOut_11_id |
            | transactions_moneyOut_11_type |
            | transactions_moneyOut_11_amount |
            | transactions_moneyOut_11_moreDetails |
        And I save the page as "report-account-transactions-errors"    
        # right values
        When I fill in the following:
            | transactions_moneyIn_0_amount       | 125 | 
            | transactions_moneyIn_15_amount      | 200 | 
            | transactions_moneyIn_15_moreDetails | more-details-in-15  |
            | transactions_moneyOut_0_amount       | 250 | 
            | transactions_moneyOut_11_amount      | 500.50 | 
            | transactions_moneyOut_11_moreDetails | more-details-out-11 | 
        And I press "transactions_saveMoneyIn"
        Then the form should not contain an error
        # assert value saved
        And the following fields should have the corresponding values:
            | transactions_moneyIn_0_amount       | 125 | 
            | transactions_moneyIn_15_amount      | 200 | 
            | transactions_moneyIn_15_moreDetails | more-details-in-15  |
            | transactions_moneyOut_0_amount       | 250 | 
            | transactions_moneyOut_11_amount      | 500.50 | 
            | transactions_moneyOut_11_moreDetails | more-details-out-11 | 
        And I should see "£325.00" in the "moneyIn-total" region
        And I should see "£750.50" in the "moneyOut-total" region
        And I should see "£-375.50" in the "money-totals" region
        And I save the page as "report-account-transactions-data-saved"