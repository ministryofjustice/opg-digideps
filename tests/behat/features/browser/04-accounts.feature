Feature: browser - accounts

    @browser
    Scenario: add account
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I add the following bank account:
            | bank    | HSBC - main account  |
            | accountNumber | 9999 |
            | accountType | Current |
            | sortCode | 11 | 22 | 33 |
            | openingBalance  | 100 |
            | closingBalance  | 100 |
        And I press "account_save"
        Then I should see "HSBC - main account" in the "list-accounts" region
        
    @browser
    Scenario: Add some money in transactions
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "edit-accounts"
        Then I follow "account-moneyin"
        And I click on the "Income and earning" section summary
        Then I enter "100" into the "y" field
        And I pause
        Then I should see "Saved" in section title info panel
        And the "field" value should be "100.00"

    @browser
    Scenario: Add some money out transactions
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "edit-accounts"
        Then I follow "account-moneyin"
        And I click on the "Accommodation" section summary
        Then I enter "100" into the "y" field
        And I pause
        Then I should see "Saved" in section title info panel
        And the "field" value should be "100.00"

        
