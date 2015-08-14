Feature: admin

    @deputy
    Scenario: login and add deputy user
        Given I reset the email log
        #And I am on "http://digideps-admin.local/app_dev.php/"
        Given I am on admin login page
        And I save the page as "admin-login"
        Then the response status code should be 200
        # test wrong credentials
        When I fill in the following:
            | login_email     | admin@publicguardian.gsi.gov.uk |
            | login_password  |  WRONG PASSWORD !! |
        And I click on "login"
        Then I should see the "header errors" region
        And I save the page as "admin-login-error1"
        # test user email in caps
        When I fill in the following:
            | login_email     | ADMIN@PUBLICGUARDIAN.GSI.GOV.UK |
            | login_password  | Abcd1234 |
        And I click on "login"
        Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
        #When I go to "http://digideps-admin.local/app_dev.php/logout"
        Given I am not logged into admin
        # test right credentials
        When I fill in the following:
            | login_email     | admin@publicguardian.gsi.gov.uk |
            | login_password  | Abcd1234 |
        And I click on "login"
        #When I go to "/admin"
        Given I am on admin page "/admin"
        # invalid email
        When I fill in the following:
            | admin_email | invalidEmail |
            | admin_firstname | 1 |
            | admin_lastname | 2 |
            | admin_roleId | 2 |
        And I press "admin_save"
        Then the form should be invalid
        And I save the page as "admin-deputy-error1"
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
        And the last email containing a link matching "/user/activate/" should have been sent to "behat-user@publicguardian.gsi.gov.uk"

    @admin
    Scenario: login and add admin user, check audit log
        Given I reset the email log
        And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then the last audit log entry should contain:
          | from | admin@publicguardian.gsi.gov.uk |
          | action | login |
        #When I go to "/admin"
        Given I am on admin page "/admin"
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
        And the last email containing a link matching "/user/activate/" should have been sent to "behat-admin-user@publicguardian.gsi.gov.uk"
        And the last audit log entry should contain:
          | from | admin@publicguardian.gsi.gov.uk |
          | action | user_add |
          | user_affected | behat-admin-user@publicguardian.gsi.gov.uk |
        #When I go to "/logout"
        Given I am on admin page "/logout"
        Then the last audit log entry should contain:
          | from | admin@publicguardian.gsi.gov.uk |
          | action | logout |

    @formatted-report @accounts @deputy
    Scenario: Setup the test user
      Given I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
      Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
      When I fill in the following:
          | admin_email | behat-report@publicguardian.gsi.gov.uk | 
          | admin_firstname | Wilma | 
          | admin_lastname | Smith | 
          | admin_roleId | 2 |
      And I click on "save"
      Then I should see "behat-report@publicguardian.gsi.gov.uk" in the "users" region
      Then I should see "Wilma Smith" in the "users" region
      Given I am on "/logout"
      When I open the "/user/activate/" link from the email
      Then the response status code should be 200
      When I fill in the following: 
          | set_password_password_first   | Abcd1234 |
          | set_password_password_second  | Abcd1234 |
      And I press "set_password_save"
      Then the form should be valid
      #Then I should be on "user/details"
      When I fill in the following:
          | user_details_firstname | John |
          | user_details_lastname | Doe |
          | user_details_address1 | 102 Petty France |
          | user_details_address2 | MOJ |
          | user_details_address3 | London |
          | user_details_addressPostcode | SW1H 9AJ |
          | user_details_addressCountry | GB |
          | user_details_phoneMain | 020 3334 3555  |
          | user_details_phoneAlternative | 020 1234 5678  |
      And I press "user_details_save"
      Then the form should be valid
      When I fill in the following:
          | client_firstname | Peter |
          | client_lastname | White |
          | client_caseNumber | 123456ABC |
          | client_courtDate_day | 1 |
          | client_courtDate_month | 1 |
          | client_courtDate_year | 2014 |
          | client_allowedCourtOrderTypes_0 | 2 |
          | client_address |  1 South Parade |
          | client_address2 | First Floor  |
          | client_county | Nottingham  |
          | client_postcode | NG1 2HT  |
          | client_country | GB |
          | client_phone | 0123456789  |
      And I press "client_save"
      Then the form should be valid
      When I fill in the following:
          | report_endDate_day | 1 |
          | report_endDate_month | 1 |
          | report_endDate_year | 2015 |
      And I press "report_save"
      Then the form should be valid
      # assert you are on dashboard
      And the URL should match "report/\d+/overview"
      Then I save the application status into "reportuser"