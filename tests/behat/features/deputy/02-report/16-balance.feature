Feature: deputy / report / balance

    @deputy
    Scenario: balance
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        # assert report not submittable
        And I click on "reports, report-2016-open"
        Then the report should not be submittable
        # assert balance is bad
        When I follow "edit-accounts"
        And I follow "account-balance"
        Then I should see the "balance-bad" region
        And I should see "105.00" in the "unaccounted-for" region
        # fix and assert is good
        When I follow "account-moneyin"
        And I fill in "transactions_transactionsIn_2_amounts_0" with "105"
        And I press "transactions_save"
        And I follow "account-balance"
        Then I should see the "balance-good" region
        
       
        
        