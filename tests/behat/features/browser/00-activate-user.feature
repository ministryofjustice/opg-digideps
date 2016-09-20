Feature: Browser - add and activate user

    @browser
    Scenario: browser - login and add deputy user
        Given emails are sent from "deputy" area
        And I reset the email log
        Given I am on admin login page
        When I fill in the following:
            | login_email     | ADMIN@PUBLICGUARDIAN.GSI.GOV.UK |
            | login_password  | Abcd1234 |
        Then I click on "login"
        And I am on admin page "/admin"
        Then I create a new "ODR-disabled" "Lay Deputy" user "John" "Doe" with email "behat-user@publicguardian.gsi.gov.uk"

    @browser
    Scenario: browser - view the homepage and login page
        Given I am on "/"
        Then I save the page as "home"
        Then I am on "/login"
        And I save the page as "login"
        
    @browser
    Scenario: browser - Set user password
        Given I am on "/logout"
        And I open the "/user/activate/" link from the email
        And I activate the user with password "Abcd1234"
        Then I set the user details to:
            | name | John | Doe |
            | address | 102 Petty France | MOJ | London | SW1H 9AJ | GB |
            | phone | 020 3334 3555  | 020 1234 5678  |
        And I set the client details to:
            | name | Peter | White |
            | caseNumber | 12345ABC |
            | courtDate | 1 | 1 | 2014 |
            | allowedCourtOrderTypes_0 | 1 |
            | address |  1 South Parade | First Floor  | Nottingham  | NG1 2HT  | GB |
            | phone | 0123456789  |
        And I pause
        And I save the page as "report-period"
        Then I fill in the following:
            | report_startDate_day | 01 |
            | report_startDate_month | 01 |
            | report_startDate_year | 2014 |
            | report_endDate_day | 01 |
            | report_endDate_month | 01 |
            | report_endDate_year | 2015 |
        And I press "report_save"
        Then the URL should match "report/\d+/overview"
