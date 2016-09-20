Feature: deputy / user / add client and report
    
    @deputy
    Scenario: add client
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "client/add"
        And I save the page as "deputy-step3"
        # submit empty form and check errors
        When I press "client_save"
        Then the following fields should have an error:
            | client_firstname |
            | client_lastname |
            | client_courtDate_day |
            | client_courtDate_month |
            | client_courtDate_year |
            | client_allowedCourtOrderTypes_0 |
            | client_allowedCourtOrderTypes_1 |
            | client_caseNumber |
            | client_caseNumber |
            | client_address |
            | client_postcode | 
        And I press "client_save"
        Then the following fields should have an error:
            | client_firstname |
            | client_lastname |
            | client_courtDate_day |
            | client_courtDate_month |
            | client_courtDate_year |
            | client_allowedCourtOrderTypes_0 |
            | client_allowedCourtOrderTypes_1 |
            | client_caseNumber |
            | client_caseNumber |
            | client_address |
            | client_postcode | 
        And I save the page as "deputy-step3-errors-empty"
        # subit invalid values and check errors
        When I press "client_save"
        When I fill in the following:
            | client_firstname | 01234567890-01234567890-01234567890-01234567890-01234567890 more than 50 chars |
            | client_lastname | 01234567890-01234567890-01234567890-01234567890-01234567890 more than 50 chars |
            | client_caseNumber | 01234567890-01234567890 more than 20 chars |
            | client_courtDate_day |99 |
            | client_courtDate_month | aa |
            | client_courtDate_year | 0986789 |
            | client_address |  01234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-01234567890 more than 200 chars |
            | client_address2 |  01234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-01234567890 more than 200 chars |
            | client_county |  01234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-01234567890 more than 200 chars |
            | client_postcode | 01234567890 more than 10 chars | 
            | client_phone | 01234567890-01234567890 more than 20 chars |
        And I press "client_save"
        Then the following fields should have an error:
          | client_firstname | 
            | client_lastname |
            | client_caseNumber |
            | client_courtDate_day |
            | client_courtDate_month |
            | client_courtDate_year |
            | client_address |
            | client_address2 |
            | client_county |
            | client_postcode |
            | client_allowedCourtOrderTypes_0 |
            | client_allowedCourtOrderTypes_1 |
            | client_phone | 
        And I save the page as "deputy-step3-errors"
        # right values
       When I set the client details to:
            | name | Peter | White |  | | |
            | caseNumber | 12345ABC | | | | |
            | courtDate | 1 | 1 | 2016 | | |
            # only tick Property and Affairs 
            # if  Personal Welfare  is re-enabled, select the other one, then de-comment next feature block (about changing COT)
            | allowedCourtOrderTypes_0 | 2 | | | | |
            | address |  1 South Parade | First Floor  | Nottingham  | NG1 2HT  | GB |
            | phone | 0123456789  | | | | |
        Then the URL should match "report/create/\d+"
        When I go to "client/add"
        Then the following fields should have the corresponding values:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 12345ABC |
            | client_courtDate_day | 01 |
            | client_courtDate_month | 01 |
            | client_courtDate_year | 2016 |
            | client_allowedCourtOrderTypes_0 | 2 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |

    @odr
    Scenario: add client (odr)
        Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "client/add"
        And I save the page as "deputy-step3"
        # right values
        When I set the client details to:
            | name | John | Green ODR |  | | |
            | caseNumber | 12345ABC | | | | |
            | courtDate | 1 | 1 | 2016 | | |
            | allowedCourtOrderTypes_0 | 2 | | | | |
            | address |  1 South Parade | First Floor  | Nottingham  | NG1 2HT  | GB |
            | phone | 0123456789  | | | | |


    @deputy    
    Scenario: create report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then the URL should match "report/create/\d+"
        And I save the page as "deputy-step4"
        # missing D,M,Y
        When I fill in the following:
            | report_startDate_day | 01 |
            | report_startDate_month | 01 |
            | report_startDate_year | 2016 |
            | report_endDate_day |  |
            | report_endDate_month |  |
            | report_endDate_year |  |
        And I press "report_save"
        Then the following fields should have an error:
            | report_endDate_day |
            | report_endDate_month |
            | report_endDate_year |
        And I press "report_save"
        Then the form should be invalid
        # invalid date
        When I fill in the following:
            | report_startDate_day | 01 |
            | report_startDate_month | 01 |
            | report_startDate_year | 2016 |
            | report_endDate_day | 99 |
            | report_endDate_month | 99 |
            | report_endDate_year | 2016 |
        And I press "report_save"
        Then the form should be invalid
        # date before report
        When I fill in the following:
            | report_startDate_day | 01 |
            | report_startDate_month | 01 |
            | report_startDate_year | 2016 |
            | report_endDate_day | 31 |
            | report_endDate_month | 12 |
            | report_endDate_year | 2010 |
        And I press "report_save"
        Then the form should be invalid
        # date range too high
        When I fill in the following:
            | report_startDate_day | 01 |
            | report_startDate_month | 01 |
            | report_startDate_year | 2016 |
            | report_endDate_day | 31 |
            | report_endDate_month | 12 |
            | report_endDate_year | 2020 |
        And I press "report_save"
        Then the form should be invalid
        And I save the page as "deputy-step4-error"
        # valid form
        Then I fill in the following:
            | report_startDate_day | 01 |
            | report_startDate_month | 01 |
            | report_startDate_year | 2016 |
            | report_endDate_day | 31 |
            | report_endDate_month | 12 |
            | report_endDate_year | 2016 |
        And I press "report_save"
        Then the URL should match "report/\d+/overview"

    @deputy
    Scenario: report-overview
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Given I click on "reports, report-2016-open" 
        Then the URL should match "report/\d+/overview"
        And I save the page as "deputy-report-overview"
