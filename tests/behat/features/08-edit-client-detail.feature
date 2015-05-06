Feature: edit client details

    @deputy
    Scenario: edit client details
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "client/show"
        And I click on "edit-client-details"
        Then I should be on "client/show/edit-client#edit-client"
        Then the following fields should have the corresponding values:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 1 |
            | client_courtDate_month | 1 |
            | client_courtDate_year | 2015 |
            | client_allowedCourtOrderTypes_1 | 1 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        When I fill in the following:
            | client_firstname | |
            | client_lastname |  |
            | client_caseNumber |  |
            | client_courtDate_day | |
            | client_courtDate_month | |
            | client_courtDate_year | |
            | client_address |  |
            | client_address2 |  |
            | client_county | |
            | client_postcode | |
            | client_country | |
            | client_phone | aaa |
        And I press "client_save"
        Then the following fields should have an error:
            | client_firstname |
            | client_lastname |
            | client_courtDate_day |
            | client_courtDate_month |
            | client_courtDate_year |
            | client_caseNumber |
            | client_caseNumber |
            | client_address |
            | client_postcode |
            | client_phone |
        When I fill in the following:
            | client_firstname | Nolan |
            | client_lastname | Ross |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 1 |
            | client_courtDate_month | 1 |
            | client_courtDate_year | 2015 |
            | client_allowedCourtOrderTypes_1 | 1 |
            | client_address |  2 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I press "client_save"
        Then I should be on "client/show"
        Then I should see "Nolan Ross" in the "client-name" region
        Then I should see "123456ABC" in the "case-number" region
        Then I should see "2 South Parade" in the "client-address" region
        