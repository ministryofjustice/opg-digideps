Feature: add client and report
    
    @deputy
    Scenario: add client
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "client/add"
        And I save the page as "deputy-step3"
        # form errors
        When I submit the form
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
        And I submit the form
        Then the form should contain an error
        And I save the page as "deputy-step3-error"
        # right values
        When I fill in the following:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 1 |
            | client_courtDate_month | 1 |
            | client_courtDate_year | 2015 |
            | client_allowedCourtOrderTypes_0 | 2 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I submit the form
        Then the form should not contain an error
        # assert you are on create report page
        And the URL should match "report/create/\d+"

    @deputy    
    Scenario: create report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then the URL should match "report/create/\d+"
        And I save the page as "deputy-step4"
        Then the following fields should have the corresponding values:
            | report_startDate_day | 1 |
            | report_startDate_month | 1 |
            | report_startDate_year | 2015 |
        # missing D,M,Y
        When I fill in the following:
            | report_endDate_day |  |
            | report_endDate_month |  |
            | report_endDate_year |  |
        And I submit the form
        Then the following fields should have an error:
            | report_endDate_day |
            | report_endDate_month |
            | report_endDate_year |
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
        And I save the page as "deputy-step4-error"
        # valid form
        When I fill in the following:
            | report_endDate_day | 31 |
            | report_endDate_month | 12 |
            | report_endDate_year | 2015 |
        And I submit the form
        Then the form should not contain an error
        # assert you are on dashboard
        And the URL should match "report/\d+/overview"


    @deputy
    Scenario: report overview
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then the URL should match "report/\d+/overview"
        And I save the page as "deputy-report-overview"
    
    
    @deputy
    Scenario: check homepage redirect
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then the URL should match "report/\d+/overview"
        When I go to "/"
        Then the URL should match "report/\d+/overview"
