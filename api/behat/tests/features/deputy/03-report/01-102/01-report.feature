Feature: Report edit and test tabs

    @deputy
    Scenario: Initialise report
        Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "DigidepsPass1234"
    # valid values
        When I fill in the following:
            | report_startDate_day | 01 |
            | report_startDate_month | 03 |
            | report_startDate_year | 2016 |
            | report_endDate_day | 31 |
            | report_endDate_month | 12 |
            | report_endDate_year | 2016 |
        And I press "report_save"
        Then the form should be valid

    @deputy
    Scenario: edit report dates
        Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "DigidepsPass1234"
        And I click on "report-edit-period"
        Then the following fields should have the corresponding values:
            | report_edit_startDate_day | 01 |
            | report_edit_startDate_month | 03 |
            | report_edit_startDate_year | 2016 |
            | report_edit_endDate_day | 31 |
            | report_edit_endDate_month | 12 |
            | report_edit_endDate_year | 2016 |
        # check validations
        When I fill in the following:
            | report_edit_startDate_day | aa |
            | report_edit_startDate_month | bb |
            | report_edit_startDate_year | c |
            | report_edit_endDate_day |  |
            | report_edit_endDate_month |  |
            | report_edit_endDate_year |  |
        And I press "report_edit_save"
        Then the following fields should have an error:
           | report_edit_startDate_day |
            | report_edit_startDate_month |
            | report_edit_startDate_year |
            | report_edit_endDate_day |
            | report_edit_endDate_month |
            | report_edit_endDate_year |
        # valid values
        When I fill in the following:
            | report_edit_startDate_day | 01 |
            | report_edit_startDate_month | 02 |
            | report_edit_startDate_year | 2016 |
            | report_edit_endDate_day | 31 |
            | report_edit_endDate_month | 12 |
            | report_edit_endDate_year | 2016 |
        And I press "report_edit_save"
        Then the form should be valid
        # check values
        And I click on "report-edit-period"
        Then the following fields should have the corresponding values:
            | report_edit_startDate_day | 01 |
            | report_edit_startDate_month | 02 |
            | report_edit_startDate_year | 2016 |
            | report_edit_endDate_day | 31 |
            | report_edit_endDate_month | 12 |
            | report_edit_endDate_year | 2016 |


    @deputy
    Scenario: test tabs for "Property and Affairs" report
        Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "DigidepsPass1234"
        And I click on "report-start"
        Then I should see the "edit-decisions" link
        Then I should see the "edit-contacts" link
        Then I should see the "edit-debts" link
        Then I should see the "edit-visits_care" link
        Then I should see the "edit-bank_accounts" link
        Then I should see the "edit-money_in" link
        Then I should see the "edit-money_out" link
        Then I should see the "edit-money_transfers" link
        Then I should see the "edit-actions" link

    @deputy
    Scenario: Check 102, 103 sections presence on overview page
        Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "DigidepsPass1234"
        And I click on "report-start"
        # assert tabs
        And I should see the "edit-decisions" link
        Then I should see the "edit-contacts" link
        Then I should see the "edit-visits_care" link
        Then I should see the "edit-deputy_expenses" link
        Then I should see the "edit-gifts" link
        Then I should see the "edit-bank_accounts" link
        Then I should see the "edit-money_transfers" link
        Then I should see the "edit-money_in" link
        Then I should see the "edit-money_out" link
        Then I should see the "edit-assets" link
        Then I should see the "edit-debts" link
        Then I should see the "edit-actions" link
        Then I should see the "edit-other_info" link
        Then I should see the "edit-documents" link
