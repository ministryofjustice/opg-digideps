Feature: admin

    Scenario: login and add user
        Given I am on "/"
        Then the page title should be "Login"
        Then the response status code should be 200
        # test wrong credentials
        When I fill in the following: 
            | email     | deputyshipservice@publicguardian.gsi.gov.uk |
            | password  |  WRONG PASSWORD !! |
        And I click on "login"
        Then I should see "invalid credentials" in the "header errors" region
        # test right credentials
        When I fill in the following:
            | email     | deputyshipservice@publicguardian.gsi.gov.uk |
            | password  |  test |
        And I click on "login"
        Then I should be on "/"
        
        # admin
        When I go to "/admin"
        Then the page title should be "Admin area"
        And I should not see "behat-user@publicguardian.gsi.gov.uk" in the "users" region
        # assert form error
        When I fill in the following:
            | form_email | invalidEmail | 
            | form_firstname | 1 | 
            | form_lastname | 2 | 
        And I press "form_save"
        Then I should see "is not a valid email"
        And I should see "Your first name must be at least 2 characters long"
        And I should see "Your last name must be at least 2 characters long"
        And I should not see "invalidEmail" in the "users" region 
        # assert form OK
        When I fill in the following:
            | form_email | behat-user@publicguardian.gsi.gov.uk | 
            | form_firstname | John | 
            | form_lastname | Doe | 
        And I press "form_save"
        Then I should see "behat-user@publicguardian.gsi.gov.uk" in the "users" region
        
        
            
        