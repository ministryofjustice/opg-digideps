Feature: Add Another
    
    @another
    Scenario: setup add another user
        Given I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
        Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
        When I fill in the following:
            | admin_email | behat-another@publicguardian.gsi.gov.uk | 
            | admin_firstname | Another | 
            | admin_lastname | Brown | 
            | admin_roleId | 2 |
        And I click on "save"
        Then I should see "behat-another@publicguardian.gsi.gov.uk" in the "users" region
        Then I should see "Another Brown" in the "users" region
        Given I am on "/logout"
        When I open the "/user/activate/" link from the email
        Then the response status code should be 200
        When I fill in the following: 
            | set_password_password_first   | Abcd1234 |
            | set_password_password_second  | Abcd1234 |
        And I press "set_password_save"
        Then the form should be valid
        Then I should be on "user/details"
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
