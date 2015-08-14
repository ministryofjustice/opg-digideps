Feature: Accounts

    @accounts @deputy
    Scenario: Setup the test user
      Given I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
      Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
      When I fill in the following:
          | admin_email | behat-account@publicguardian.gsi.gov.uk | 
          | admin_firstname | Account | 
          | admin_lastname | Smith | 
          | admin_roleId | 2 |
      And I click on "save"
      Then I should see "behat-account@publicguardian.gsi.gov.uk" in the "users" region
      Then I should see "Account Smith" in the "users" region
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
      Then I am on "/logout"
      And I reset the email log
      Then I save the application status into "accountuser"
      
    @accounts @deputy
    Scenario: change opening balance explanation
        When I load the application status from "accountuser"
        And I am logged in as "behat-account@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I follow "tab-accounts"
        # Create an account with an opening balance explanation
        And I fill in the following:
            | account_bank    | HSBC - main account | 
            | account_accountNumber_part_1 | 9 | 
            | account_accountNumber_part_2 | 9 | 
            | account_accountNumber_part_3 | 1 | 
            | account_accountNumber_part_4 | 1 | 
            | account_sortCode_sort_code_part_1 | 22 |
            | account_sortCode_sort_code_part_2 | 22 |
            | account_sortCode_sort_code_part_3 | 22 |
            | account_openingDate_day   | 1 |
            | account_openingDate_month | 2 |
            | account_openingDate_year  | 2014 |
            | account_openingBalance  | 100.00 |
            | account_openingDateExplanation | Test One |
        And I press "account_save"
        And the form should be valid
        # Goto the account and edit it to see the account opening explanation
        And I click on "account-9911"
        Then I click on "edit-account-details"
        And the "account_openingDateExplanation" field should contain "Test One"
        # Now fill in the account transactions and closing balance.
        Then I fill in the following:
            | transactions_moneyIn_0_amount       | 1 |
            | transactions_moneyOut_0_amount      | 1 |
        And I press "transactions_saveMoneyOut"
        Then I fill in the following:
            | accountBalance_closingDate_day   | 1 | 
            | accountBalance_closingDate_month | 1 | 
            | accountBalance_closingDate_year  | 2015 | 
            | accountBalance_closingBalance    | 100.00 |
        And I press "accountBalance_save"
        And I click on "edit-account-details"
        Then the "account_openingDateExplanation" field should contain "Test One"
        And I fill in the following:
            | account_openingDateExplanation | Test Two |
        Then I press "account_save"
        And I click on "edit-account-details"
        Then the "account_openingDateExplanation" field should contain "Test Two"