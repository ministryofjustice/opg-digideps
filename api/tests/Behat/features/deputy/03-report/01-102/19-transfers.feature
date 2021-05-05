Feature: Report account transfers

  @deputy
  Scenario: transfers
    Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "report-start, edit-money_transfers, start"
      # chose "no records"
    Given the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | yes_no_noTransfersToAdd_1 | 1 |
      # summary page check
    And each text should be present in the corresponding region:
      | No | no-transfers-to-add |
      # select there are records (from summary page link)
    Given I click on "edit" in the "no-transfers-to-add" region
    Then the step with the following values CAN be submitted:
      | yes_no_noTransfersToAdd_0 | 0 |
      # add transfer n.1 (and validate form)
    And the step cannot be submitted without making a selection
    When I select "HSBC - saving account - Savings account (****02ca)" from "money_transfers_type_accountFromId"
    And I select "HSBC - saving account - Savings account (****02ca)" from "money_transfers_type_accountToId"
    And I submit the step
    Then the form should be invalid
    When I select "HSBC - saving account - Savings account (****02ca)" from "money_transfers_type_accountFromId"
    And I select "Court Funds Office account (****11cf)" from "money_transfers_type_accountToId"
    And I submit the step
    Then the form should be valid
    And the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | money_transfers_type_amount | asasd |
    And the step with the following values CAN be submitted:
      | money_transfers_type_amount | 1234.56 |
      # add another: yes
    And I choose "yes" when asked for adding another record
      # add transfer n.2
    When I select "HSBC - saving account - Savings account (****02ca)" from "money_transfers_type_accountFromId"
    And I select "Court Funds Office account (****11cf)" from "money_transfers_type_accountToId"
    And I submit the step
    Then the form should be valid
    And the step with the following values CAN be submitted:
      | money_transfers_type_amount | 98.76 |
      # add another: no
    And I choose "no" when asked for adding another record
    #check record in summary page
    And each text should be present in the corresponding region:
      | £1,234.56 | transfer-02ca-11cf-123456 |
      | £98.76    | transfer-02ca-11cf-9876   |
      # remove transfer n.2
    When I click on "delete" in the "transfer-02ca-11cf-9876" region
    And I click on "confirm"
    Then I should not see the "transfer-02ca-11cf-9876" region
      # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
      # edit transfer n.1
    When I click on "edit" in the "transfer-02ca-11cf-123456" region
    Then I should see "HSBC - saving account - Savings account (****02ca)" in the "#money_transfers_type_accountFromId" element
    And I should see "Court Funds Office account (****11cf)" in the "#money_transfers_type_accountToId" element
    When I select "Court Funds Office account (****11cf)" from "money_transfers_type_accountFromId"
    And I select "HSBC - saving account - Savings account (****02ca)" from "money_transfers_type_accountToId"
    And I submit the step
    Then the form should be valid
    Then the following fields should have the corresponding values:
      | money_transfers_type_amount | 1,234.56 |
    And the step with the following values CAN be submitted:
      | money_transfers_type_amount | 1,234.57 |
    And each text should be present in the corresponding region:
      | £1,234.57 | transfer-11cf-02ca-123457 |


  @deputy
    Scenario: Remove account with transfers
      # navigate to accounts list (that now have transfers)
      Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "DigidepsPass1234"
      And I click on "report-start, edit-bank_accounts"
      And I click on "delete" in the "account-11cf" region
      # Account still visible
      Then I should see the "account-11cf" region
      # should have 2 transactions errors 1 error for a transfer and 1 expense
      And I should see the "error" region
