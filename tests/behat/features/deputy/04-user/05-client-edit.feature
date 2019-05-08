Feature: deputy / report / edit client

    @deputy
    Scenario: edit client details
        Given I load the application status from "report-submit-pre"
        And I am logged in as "behat-user@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "user-account, client-show, client-edit"
        Then the following fields should have the corresponding values:
            | client_firstname | Cly |
            | client_lastname | Hent |
            | client_courtDate_day | 01 |
            | client_courtDate_month | 01 |
            | client_courtDate_year | 2016 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        When I fill in the following:
            | client_firstname | |
            | client_lastname |  |
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
            | client_address |
            | client_postcode |
            | client_phone |
        When I fill in the following:
            | client_firstname | Nolan |
            | client_lastname | Ross |
            | client_courtDate_day | 1 |
            | client_courtDate_month | 1 |
            | client_courtDate_year | 2016 |
            | client_address |  2 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I press "client_save"
        Then I should be on "/deputyship-details/your-client"
        And I should see "NG1 2HT" in the "client-address-postcode" region
        When I click on "client-edit"
        Then the following fields should have the corresponding values:
            | client_firstname | Nolan |

    # @deputy
    # Scenario: edit client details
    #     Given emails are sent from "deputy" area
    #     And I am logged in as "laydeputy@publicguardian.gov.uk" with password "Abcd1234"
    #     And I click on "user-account, client-show, client-edit"
    #     When I fill in the following:
    #         | client_firstname | Ulrich |
    #         | client_lastname | Wentz |
    #         | client_address |  17 South Parade |
    #         | client_address2 | Second Floor |
    #         | client_county | Nottingham |
    #         | client_postcode | NG1 2HT |
    #         | client_country | VE |
    #         | client_phone | 0987654321 |
    #     And I press "client_save"
    #     Then I should be on "/deputyship-details/your-client"
    #     And the last email should have been sent to "digideps+update-contact@digital.justice.gov.uk"
    #     And the last email should contain "The contact details of the following client have been updated:"
    #     And the last email should contain "Ulrich Wentz"
    #     And the last email should contain "17 South Parade"
    #     And the last email should contain "Venezuela"
    #     And the last email should contain "0987654321"
