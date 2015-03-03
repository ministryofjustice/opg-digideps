Feature: admin
    
    @cleanMail @deputy
    Scenario: login and add deputy user
        Given I am on "/"
        Then the response status code should be 200
        # test wrong credentials
        When I fill in the following: 
            | login_email     | deputyshipservice@publicguardian.gsi.gov.uk |
            | login_password  |  WRONG PASSWORD !! |
        And I click on "login"
        Then I should see the "header errors" region
        # test right credentials
        When I fill in the following:
            | login_email     | deputyshipservice@publicguardian.gsi.gov.uk |
            | login_password  |  test |
        And I click on "login"
        When I go to "/admin"
        And I should not see "behat-user@publicguardian.gsi.gov.uk" in the "users" region
        # invalid email
        When I fill in the following:
            | admin_email | invalidEmail | 
            | admin_firstname | 1 | 
            | admin_lastname | 2 | 
            | admin_roleId | 2 |
        And I press "admin_save"
        Then I should see "is not a valid email"
        And I should see "Your first name must be at least 2 characters long"
        And I should see "Your last name must be at least 2 characters long"
        And I should not see "invalidEmail" in the "users" region 
        # assert form OK
        When I fill in the following:
            | admin_email | behat-user@publicguardian.gsi.gov.uk | 
            | admin_firstname | John | 
            | admin_lastname | Doe | 
            | admin_roleId | 2 |
        And I click on "save"
        Then I should see "behat-user@publicguardian.gsi.gov.uk" in the "users" region
        Then I should see "Lay Deputy" in the "users" region
        And I save the page as "admin-deputy-added"
        And an email with subject "Digideps - activation email" should have been sent to "behat-user@publicguardian.gsi.gov.uk"
        
        
    @cleanMail @admin
    Scenario: login and add admin user
        Given I am logged in as "deputyshipservice@publicguardian.gsi.gov.uk" with password "test"
        When I go to "/admin"
        And I fill in the following:
            | admin_email | behat-admin-user@publicguardian.gsi.gov.uk | 
            | admin_firstname | John | 
            | admin_lastname | Doe | 
            | admin_roleId | 1 |
        And I click on "save"
        Then I should see "behat-admin-user@publicguardian.gsi.gov.uk" in the "users" region
        Then the response status code should be 200
        And I should see "OPG Administrator" in the "users" region
        And I save the page as "admin-admin-added"
        And an email with subject "Digideps - activation email" should have been sent to "behat-admin-user@publicguardian.gsi.gov.uk"
        
