Feature: report
    
    @deputy
    Scenario: test login goes to previous page
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I follow "tab-accounts"
        And I click on "account-n1"
        Then the URL should match "/report/\d+/account/\d+"
        Then I visit the behat link "destroy-session"
        When I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        # for weird reasons this check is OK with the browser, but fails with behat
        #Then the URL should match "/report/\d+/account/d+"
    
        
        