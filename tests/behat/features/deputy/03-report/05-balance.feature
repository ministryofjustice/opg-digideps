Feature: deputy / report / account transactions

    @deputy
    Scenario: money in 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on the accounts page of the "2015" report
        And I follow "account-balance"
        Then I should see "£105.00" in the "unaccounted-for" region