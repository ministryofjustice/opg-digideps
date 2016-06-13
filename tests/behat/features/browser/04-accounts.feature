Feature: browser - accounts
    
    @browser
    Scenario: browser - add account
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #Add a bank account
        And I add the following bank account:
            | bank    | HSBC - main account  |
            | accountNumber | 9999 |
            | accountType | Current |
            | sortCode | 11 | 22 | 33 |
            | openingBalance  | 100 |
            | closingBalance  | 100 |
        And I save the page as "accounts-list"
        Then I should see "HSBC - main account" in the "list-accounts" region
        # Test money in autosave and formatting
        Then I follow "account-moneyin"
        And I click on "income-and-earnings"
        And I save the page as "moneyin-1"
        Then I fill in "transactions_transactionsIn_0_amounts_0" with "100"
        And I tab to the next field
        And I pause
        And I save the page as "moneyin-2"
        Then I should see "Saved" in the section title info panel
        And the "transactions_transactionsIn_0_amounts_0" field should contain "100.00"
        And I follow "account-moneyin"
        And the "transactions_transactionsIn_0_amounts_0" field should contain "100.00"
        # Test money out autosave and formatting
        Then I follow "account-moneyout"
        And I click on "accommodation"
        And I save the page as "moneyout-1"
        Then I fill in "transactions_transactionsOut_13_amounts_0" with "101"
        And I tab to the next field
        And I pause
        Then I should see "Saved" in the section title info panel
        And I save the page as "moneyout-2"
        And the "transactions_transactionsOut_13_amounts_0" field should contain "101.00"
        Then I follow "account-moneyout"
        And the "transactions_transactionsOut_13_amounts_0" field should contain "101.00"
        # Test bad balance screen
        Then I follow "account-balance"
        And I pause
        And I save the page as "bad-balance"
        And I pause
        # Test good balance screen
        Then I follow "account-moneyout"
        And I click on "accommodation"
        Then I fill in "transactions_transactionsOut_13_amounts_0" with "100"
        And I tab to the next field
        And I pause
        Then I follow "account-balance"
        And I save the page as "good-balance"
