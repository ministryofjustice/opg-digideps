Feature: Terßms and Conditions
    
    @deputy
    Scenario: The footer provides a link to terms and conditions in the login page
        Given I am on the login page
        Then the "Terms and Conditions" link, in the footer, url should contain "/terms"
    
    @deputy
    Scenario: The footer provides a link to the terms and conditions when logged in
        Given I am logged in as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then the "Terms and Conditions" link, in the footer, url should contain "/terms"
        
    @deputy
    Scenario: The terms and conditions page contains a back link
        Given I am on the login page
        And I go to "/terms"
        Then the "Back" link url should contain "/login"
    
