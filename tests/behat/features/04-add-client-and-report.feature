Feature: add client and report
    
    Scenario: add client
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "client/add"
        # missing client_firstname
        When I fill in the following:
            | client_firstname |  |
            | client_lastname | White |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 01 |
            | client_courtDate_month | 01 |
            | client_courtDate_year | 2015 |
            | client_allowedCourtOrderTypes_0 | 1 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I submit the form
        Then the form should contain an error
        # missing  client_lastname
        When I fill in the following:
            | client_firstname | Peter |
            | client_lastname |  |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 01 |
            | client_courtDate_month | 01 |
            | client_courtDate_year | 2015 |
            | client_allowedCourtOrderTypes_0 | 1 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I submit the form
        Then the form should contain an error
        # missing client_courtDate_day
        When I fill in the following:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day |  |
            | client_courtDate_month | 01 |
            | client_courtDate_year | 2015 |
            | client_allowedCourtOrderTypes_0 | 1 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I submit the form
        Then the form should contain an error
        #  missing client_courtDate_month
        When I fill in the following:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 01 |
            | client_courtDate_month |  |
            | client_courtDate_year | 2015 |
            | client_allowedCourtOrderTypes_0 | 1 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I submit the form
        Then the form should contain an error
        # missing client_courtDate_year
        When I fill in the following:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 01 |
            | client_courtDate_month | 01 |
            | client_courtDate_year |  |
            | client_allowedCourtOrderTypes_0 | 1 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I submit the form
        Then the form should contain an error
        # missing court order type
        When I fill in the following:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 01 |
            | client_courtDate_month | 01 |
            | client_courtDate_year |  |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I submit the form
        Then the form should contain an error
        # right values
        When I fill in the following:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 1 |
            | client_courtDate_month | 1 |
            | client_courtDate_year | 2015 |
            | client_allowedCourtOrderTypes_0 | 1 |
            | client_allowedCourtOrderTypes_1 | 2 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I submit the form
        Then the form should not contain an error
        # assert you are on create report page
        And I should be on "report/create/1"


     Scenario: create report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "report/create/1"
        Then the following fields should have the corresponding values:
            | report_startDate_day | 1 |
            | report_startDate_month | 1 |
            | report_startDate_year | 2015 |
        # missing day
        When I fill in the following:
            | report_endDate_day |  |
            | report_endDate_month | 12 |
            | report_endDate_year | 2015 |
        And I submit the form
        Then the form should contain an error
        # missing month
        When I fill in the following:
            | report_endDate_day | 31 |
            | report_endDate_month |  |
            | report_endDate_year | 2015 |
        And I submit the form
        Then the form should contain an error
        # missing year
        When I fill in the following:
            | report_endDate_day | 31 |
            | report_endDate_month | 12 |
            | report_endDate_year |  |
        And I submit the form
        Then the form should contain an error
        # invalid date
        When I fill in the following:
            | report_endDate_day | 99 |
            | report_endDate_month | 99 |
            | report_endDate_year | 2015 |
        And I submit the form
        Then the form should contain an error
        # date before report
        When I fill in the following:
            | report_endDate_day | 31 |
            | report_endDate_month | 12 |
            | report_endDate_year | 2010 |
        And I submit the form
        Then the form should contain an error
        # date range too high
        When I fill in the following:
            | report_endDate_day | 31 |
            | report_endDate_month | 12 |
            | report_endDate_year | 2016 |
        And I submit the form
        Then the form should contain an error
        # valid form
        When I fill in the following:
            | report_endDate_day | 31 |
            | report_endDate_month | 12 |
            | report_endDate_year | 2015 |
        And I submit the form
        Then the form should not contain an error
        # assert you are on dashboard
        And I should be on "/report/overview/1"

     Scenario: report overview
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "/report/overview/1"
