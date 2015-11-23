Feature: deputy / report / closing balance


    @deputy
    Scenario: edit bank account, check edit account does not show closing balance
        Given I set the report 1 end date to 3 days ahead
#        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I am on the account "1234" page of the "2015" report
#        And I click on "edit-account-details"
#        #TODO
#
#    @deputy
#    Scenario: add closing balance to account
#        Given I set the report 1 end date to 3 days ahead
#        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I am on the accounts page of the "2015" report
#        Then I should not see the "account-1-add-closing-balance" link
#        When I set the report 1 end date to 3 days ago
#        And I am on the accounts page of the "2015" report
#        Then I should see the "account-1234-warning" region
#        And I save the page as "report-account-closing-balance-overview"
#        When I click on "account-1234"
#        And I save the page as "report-account-closing-balance-form"
#        Then I should not see a "accountBalance_closingDateExplanation" element
#        Then I should not see a "accountBalance_closingBalanceExplanation" element
#        Then the following fields should have the corresponding values:
#            | accountBalance_closingDate_day   | |
#            | accountBalance_closingDate_month | |
#            | accountBalance_closingDate_year  | |
#            | accountBalance_closingBalance    | |
#        # invalid values
#        When I fill in the following:
#            | accountBalance_closingDate_day   |  |
#            | accountBalance_closingDate_month |  |
#            | accountBalance_closingDate_year  |  |
#            | accountBalance_closingBalance    | invalid value |
#        And I press "accountBalance_save"
#        Then the following fields should have an error:
#            | accountBalance_closingDate_day   |
#            | accountBalance_closingDate_month |
#            | accountBalance_closingDate_year  |
#            | accountBalance_closingBalance    |
#        And I save the page as "report-account-closing-balance-form-errors"
#        # invalid values
#        When I fill in the following:
#            | accountBalance_closingDate_day   | 99 |
#            | accountBalance_closingDate_month | 99 |
#            | accountBalance_closingDate_year  | 1 |
#            | accountBalance_closingBalance    | 10000000001 |
#        And I press "accountBalance_save"
#        Then the following fields should have an error:
#            | accountBalance_closingDate_day   |
#            | accountBalance_closingDate_month |
#            | accountBalance_closingDate_year  |
#            | accountBalance_closingBalance    |
#            | accountBalance_closingBalanceExplanation    |
#        # only date mismatch (30 days ago instead of 3 days ago)
#        When I fill in "accountBalance_closingDate_day" with the value of "30 days ahead, DD"
#        And I fill in "accountBalance_closingDate_month" with the value of "30 days ahead, MM"
#        And I fill in "accountBalance_closingDate_year" with the value of "30 days ahead, YYYY"
#        And I fill in "accountBalance_closingBalance" with "-3100.50"
#        And I press "accountBalance_save"
#        Then the following fields should have an error:
#            | accountBalance_closingDate_day   |
#            | accountBalance_closingDate_month |
#            | accountBalance_closingDate_year  |
#            | accountBalance_closingDateExplanation |
#        And I should not see a "accountBalance_closingBalanceExplanation" element
#        # only balance mismatch (3000 instead of -3,100.50)
#        When I fill in "accountBalance_closingDate_day" with the value of "3 days ago, DD"
#        And I fill in "accountBalance_closingDate_month" with the value of "3 days ago, MM"
#        And I fill in "accountBalance_closingDate_year" with the value of "3 days ago, YYYY"
#        And I fill in "accountBalance_closingBalance" with "-3000"
#        And I press "accountBalance_save"
#        Then the following fields should have an error:
#            | accountBalance_closingBalance    |
#            | accountBalance_closingBalanceExplanation    |
#        And I should not see a "accountBalance_closingDateExplanation" element
#        # both date and balance mismatch: assert submit fails
#        When I fill in "accountBalance_closingDate_day" with the value of "30 days ahead, DD"
#        And I fill in "accountBalance_closingDate_month" with the value of "30 days ahead, MM"
#        And I fill in "accountBalance_closingDate_year" with the value of "30 days ahead, YYYY"
#        And I fill in "accountBalance_closingBalance" with "-3000"
#        And I press "accountBalance_save"
#        Then the "accountBalance_closingDateExplanation" field should be expandable
#        Then the "accountBalance_closingDateExplanation" field should be expandable
#        And the "accountBalance_closingBalanceExplanation" field should be expandable
#        Then the following fields should have an error:
#            | accountBalance_closingDate_day   |
#            | accountBalance_closingDate_month |
#            | accountBalance_closingDate_year  |
#            | accountBalance_closingDateExplanation |
#            | accountBalance_closingBalance    |
#            | accountBalance_closingBalanceExplanation    |
#        # fix date, assert only balance failes and date explanation disappear
#        When I fill in "accountBalance_closingDate_day" with the value of "3 days ago, DD"
#        And I fill in "accountBalance_closingDate_month" with the value of "3 days ago, MM"
#        And I fill in "accountBalance_closingDate_year" with the value of "3 days ago, YYYY"
#        And I press "accountBalance_save"
#        Then I should not see a "accountBalance_closingDateExplanation" element
#        And the following fields should have an error:
#            | accountBalance_closingBalance    |
#            | accountBalance_closingBalanceExplanation    |
#        # make date invalid, fix balance. assert only date fails and balance explanation disappear
#        When I fill in "accountBalance_closingDate_day" with the value of "30 days ahead, DD"
#        And I fill in "accountBalance_closingDate_month" with the value of "30 days ahead, MM"
#        And I fill in "accountBalance_closingDate_year" with the value of "30 days ahead, YYYY"
#        And I fill in "accountBalance_closingBalance" with "-3100.50"
#        And I press "accountBalance_save"
#        Then I should not see a "accountBalance_closingBalanceExplanation" element
#        And the following fields should have an error:
#            | accountBalance_closingDate_day   |
#            | accountBalance_closingDate_month |
#            | accountBalance_closingDate_year  |
#            | accountBalance_closingDateExplanation |
#        # save with both date and balance mismatch
#        # both date and balance mismatch: add explanations
#        When I fill in "accountBalance_closingDate_day" with the value of "30 days ahead, DD"
#        And I fill in "accountBalance_closingDate_month" with the value of "30 days ahead, MM"
#        And I fill in "accountBalance_closingDate_year" with the value of "30 days ahead, YYYY"
#        And I fill in "accountBalance_closingBalance" with "-3000"
#        And I press "accountBalance_save"
#        Then the form should be invalid
#        When I fill in the following:
#            | accountBalance_closingDateExplanation| not possible to login to homebanking before |
#            | accountBalance_closingBalanceExplanation| £ 100.50 moved to other account |
#        And I press "accountBalance_save"
#        Then the form should be valid
#        And I save the page as "report-account-closing-balance-form-valid"
#        # assert the form disappeared
#        Then I should not see the "account-closing-balance-form" region
#        # assert transactions are not changed due to the form in the same page
#        And I should see "£-3,100.50" in the "money-totals" region
#        And I should see "not possible to login to homebanking before" in the "closing-date-explanation" region
#        And I should see "100.50 moved to other account" in the "closing-balance-explanation" region
#        # refresh page and check values
#        When I follow "overview-button"
#        Then I follow "edit-accounts"
#        Then I should see "3,000.00" in the "account-1-closing-balance" region
#        And I should see the value of "30 days ahead, DD/MM/YYYY" in the "account-1-closing-date" region
#
#
#    @deputy
#      Scenario: edit closing balance
#        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I am on the account "1234" page of the "2015" report
#        And I click on "edit-account-details"
#        Then I save the page as "report-account-edit-after-closing"
#        Then the field "account_closingDate_day" has value of "30 days ahead, DD"
#        And the field "account_closingDate_month" has value of "30 days ahead, MM"
#        And the field "account_closingDate_year" has value of "30 days ahead, YYYY"
#        Then the following fields should have the corresponding values:
#            | account_closingDateExplanation  | not possible to login to homebanking before |
#            | account_closingBalance    | -3,000.00 |
#            | account_closingBalanceExplanation | £ 100.50 moved to other account |
#        # invalid values
#        When I fill in the following:
#            | account_closingDate_day   |  |
#            | account_closingDate_month |  |
#            | account_closingDate_year  |  |
#            | account_closingBalance    |  |
#        And I press "account_save"
#        Then the following fields should have an error:
#            | account_closingDate_day   |
#            | account_closingDate_month |
#            | account_closingDate_year  |
#            | account_closingBalance    |
#        And I should not see the "account_closingDateExplanation" element
#        And I should not see the "account_closingBalanceExplanation" element
#        And I save the page as "report-account-edit-after-closing-errors"
#        # assert explanations disappear when values are ok (bank empties to avoid submissions)
#        When go to "/report/1/account/1/edit"
#        And I fill in "account_closingDate_day" with the value of "3 days ahead, DD"
#        And I fill in "account_closingDate_month" with the value of "3 days ahead, MM"
#        And I fill in "account_closingDate_year" with the value of "3 days ahead, YYYY"
#        And I fill in the following:
#            | account_bank | |
#            | account_closingBalance    | -3100.50 |
#        And I press "account_save"
#        Then the following fields should have an error:
#            | account_bank   |
#        And I should not see the "account_closingDateExplanation" element
#        And I should not see the "account_closingBalanceExplanation" element
#        # assert explanations are required
#        When go to "/report/1/account/1/edit"
#        And I fill in the following:
#            | account_closingDateExplanation | |
#            | account_closingBalanceExplanation |  |
#        And I press "account_save"
#        Then the following fields should have an error:
#            | account_closingDate_day   |
#            | account_closingDate_month |
#            | account_closingDate_year  |
#            | account_closingDateExplanation |
#            | account_closingBalance    |
#            | account_closingBalanceExplanation |
#        # simple save
#        When go to "/report/1/account/1/edit"
#        And I fill in the following:
#            | account_closingDate_day   | 1 |
#            | account_closingDate_month | 5 |
#            | account_closingDate_year  | 2015 |
#            | account_closingDateExplanation | not possible to login to homebanking  |
#            | account_closingBalance | -3,000.50 |
#            | account_closingBalanceExplanation | £ 100 moved to other account |
#        And I press "account_save"
#        Then the form should be valid
#        And I should see "01/05/2015" in the "account-closing-balance-date" region
#        And I should see "£-3,000.50" in the "account-closing-balance" region
