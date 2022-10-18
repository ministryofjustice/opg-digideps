Feature: admin / acl


    Scenario: An admin user cannot login into deputy area
        # check admin can login into admin site. Password previously changed.
        Given I am logged in to admin as "behat-admin-user@publicguardian.gov.uk" with password "DigidepsPass1234!"
        #Then the response status code should be 200
        # check admin CANNOT login into DEPUTY site
        Given I go to "/logout"
        And  I go to "/login"
        When I fill in the following:
            | email     | behat-admin-user@publicguardian.gov.uk |
            | password  | DigidepsPass1234! |
        And I click on "login"
        Then I should see an "#error-summary" element
        And I should be on "/login"
