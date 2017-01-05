Feature: deputy / report / edit and test tabs
    
#    (enable if HW cot is re-enabled)
#    @deputy
#    Scenario: test tabs for "Health & Welfare" report
#        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I save the page as "report-health-welfare-homepage"
#        And I should see a "#edit-decisions" element
#        And I should see a "#edit-contacts" element
#        But I should not see a "#edit-bank_accounts" element
#        And I should not see a "#edit-assets" element
#        And I should see a "#edit-visits_care" element

#    @deputy
#    Scenario: change report type to "Property and Affairs"
#        Given I change the report "1" court order type to "Property and Affairs"
        
    @deputy
    Scenario: edit report dates
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports, report-2016-edit" 
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
        And I click on "report-2016-edit"
        Then the following fields should have the corresponding values:
            | report_edit_startDate_day | 01 |
            | report_edit_startDate_month | 02 |
            | report_edit_startDate_year | 2016 |
            | report_edit_endDate_day | 31 |
            | report_edit_endDate_month | 12 |
            | report_edit_endDate_year | 2016 |

    @deputy
    Scenario: test tabs for "Property and Affairs" report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I save the page as "report-property-affairs-homepage"
        Then I should see a "#edit-contacts" element
        And I should see a "#edit-decisions" element
        And I should see a "#edit-bank_accounts" element
        And I should see a "#edit-money_transfers" element
        And I should see a "#edit-money_in" element
        And I should see a "#edit-money_out" element
        And I should see a "#edit-assets" element

#    @deputy
#    Scenario: Check report notification and submission warnings
#        # set report due
##        Given I set the report 1 end date to 3 days ago
#        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        # disabled element are not visible from behat
#        And I should not see a "report_submit_submitReport" element
#        And I should not see a ".report_submission_period" element
