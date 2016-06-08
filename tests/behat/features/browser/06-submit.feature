Feature: browser - assets

    @browser
    Scenario: browser - Submit report
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I save the page as "ready-to-submit"
        
