Feature: New accounts money in

    
    @accounts @javascript
    Scenario: Set things up
        Given I am logged in as "laydeputy@publicguardian.gsi.gov.uk" with password "PDEtnLay1234"
        Then I should be on "/user/details"
        When I set the user details to:
            | name | John | Doe | | | |
            | address | 102 Petty France | MOJ | London | SW1H 9AJ | GB |
            | phone | 020 3334 3555  | 020 1234 5678  | | | |
        Then I set the client details to:
            | name | Peter | White | | | |
            | caseNumber | 123456ABC | | | | |
            | courtDate | 1 | 1 | 2016 | | |
            | allowedCourtOrderTypes_1 | 1 | | | | |
            | address |  1 South Parade | First Floor  | Nottingham  | NG1 2HT  | GB |
            | phone | 0123456789  | | | | |
        Then I fill in the following:
            | report_endDate_day | 31 |
            | report_endDate_month | 12 |
            | report_endDate_year | 2016 |
        And I press "report_save"
        Then I save the application status into "laydeputy-ready"

    @accounts @javascript
    Scenario: A Deputy can access the money in page
        Given I load the application status from "laydeputy-ready"
        And  I am logged in as "laydeputy@publicguardian.gsi.gov.uk" with password "PDEtnLay1234"
        And I go to "/accounts/1/moneyin"
        Then I should see a subsection called "fred"
        And I should see "Money coming into the client's accounts"
        And I should see "Income from investments"
        And I should see "Account interest"
        And I should see "Dividends"

