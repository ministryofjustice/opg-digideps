Feature: bank account delete

  @deputy
  Scenario: Remove account with transfers does not delete transactions
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "report-start, edit-bank_accounts"
    And I click on "delete" in the "account-03ta" region
    Then the URL should match "/report/\d+/bank-account/\d+/delete"
    And I click on "confirm"
    Then I should not see the "account-03ta" region

  @deputy
  Scenario: assert transaction associated to the bank account is not deleted
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "report-start, edit-money_out"
    Then I should see "2.00" in the "transaction-coffee" region
    But I should not see "03ta" in the "transaction-coffee" region



