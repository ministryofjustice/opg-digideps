Feature: Add Another
    
    @another
    Scenario: setup add another user
        Given I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
        Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
        When I create a new "Lay Deputy" user "Another" "Brown" with email "behat-another@publicguardian.gsi.gov.uk"
        And I activate the user with password "Abcd1234"
        And I set the user details to:
            | name | Another | Brown |
            | address | 102 Petty France | MOJ | London | SW1H 9AJ | GB |
            | phone | 020 3334 3555 | 020 1234 5678  |
        And I set the client details to:
            | name | Jillian | White | 
            | caseNumber | 123456ABC |
            | courtDate | 1 | 1 | 2014 |
            | allowedCourtOrderTypes_0 | 2 |
            | address |  1 South Parade | First Floor  | Nottingham  | NG1 2HT  | GB |
            | phone | 0123456789  |
        And I set the report end date to "1/1/2015"
        Then the URL should match "report/\d+/overview"
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
        When I add the following contact:
            | contactName | Andy White |
            | relationship | brother  |
            | explanation | no explanation |
            | address | 45 Noth Road | Islington | London | N2 5JF | GB |
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