Feature: add client
    
    Scenario: add client
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on "client/add"
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
        When I go to "client/add"
        Then the following fields should have the corresponding values:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 1 |
            | client_courtDate_month | 1 |
            | client_courtDate_year | 2015 |
            | client_allowedCourtOrderTypes_0 | 1 |
            | client_allowedCourtOrderTypes_1 | 2  |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        # check saving from filled in form works
        When I fill in the following:
            | client_lastname | Green |
        When I submit the form
        And I go to "client/add"
        Then the following fields should have the corresponding values:
            | client_lastname | Green |
