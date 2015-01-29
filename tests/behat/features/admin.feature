Feature: admin

    Scenario: login and add user
        Given I am on "/"
        Then the page title should be "Login"
        Then the response status code should be 200
        When I fill in "email" with "deputyshipservice@publicguardian.gsi.gov.uk"
        And I fill in "password" with "test"
        And I click on "login"
        Then I should be on "/"
        # admin
        When I go to "/admin"
        Then the page title should be "Add user"
        And I should not see "behat-user@publicguardian.gsi.gov.uk" in the "users" region
        When I fill in "email" with "behat-user@publicguardian.gsi.gov.uk"
        And I fill in "first_name" with "John"
        And I fill in "last_name" with "doe"
        And I click on "add user"
        Then I should see "behat-user@publicguardian.gsi.gov.uk" in the "users" region
        
            
        