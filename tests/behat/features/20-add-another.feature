Feature: Add Another
    
    @another
    Scenario: setup add another user
        Given I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
        Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
        When I create a new "Lay Deputy" user "Another" "Brown" with email "behat-another@publicguardian.gsi.gov.uk"
        And I activate the user with password "Abcd1234"
        When I fill in the following:
            | user_details_firstname | Another |
            | user_details_lastname | Brown |
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
            | client_firstname | Jillian |
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
        Then I save the application status into "anotheruser"
    
    @another
    Scenario: Adding an account changes the add button to add another
        When I load the application status from "anotheruser"
        And I am logged in as "behat-another@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-accounts"
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
            Then I should see "Add another account" in "add-account-button"
                
    @another
    Scenario: Adding a contact changes the add button to say add another contact
        When I load the application status from "anotheruser"
        And I am logged in as "behat-another@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-contacts"
        Then I should see "Add a contact" in the "add-a-contact" region
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
        Then I should see "Add another contact" in the "add-a-contact" region
                        
    @another
    Scenario: Adding a contact changes the add button to say add another contact
        When I load the application status from "anotheruser"
        And I am logged in as "behat-another@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I follow "tab-assets"
        Then I should see "Add an asset" in the "add-an-asset" region
        And I click on "add-an-asset"
        And I fill in the following:
            | asset_title       | Vehicles | 
            | asset_value       | 12000.00 | 
            | asset_description | Mini cooper | 
            | asset_valuationDate_day | 10 | 
            | asset_valuationDate_month | 11 | 
            | asset_valuationDate_year | 2015 |
        Then I press "asset_save"
        Then I should see "Add another asset" in the "add-an-asset" region

    @another
    Scenario: Adding a decision changes the add button to say add another decision
        When I load the application status from "anotheruser"
        And I am logged in as "behat-another@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-decisions"
        Then I should see "Add a decision" in the "add-a-decision" region
        Then I click on "add-a-decision"
        And I fill in the following:
            | decision_description | 3 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 85% |
        Then I press "decision_save"
        Then I should see "Add another decision" in the "add-a-decision" region