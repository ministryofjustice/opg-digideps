Feature: admin / admin
  @infra
  Scenario: login as admin
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    Then I should be on "/admin/"

  Scenario: login and add admin user
    Given I am on admin page "/"
    Then I should be on "/login"
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    Given I am on admin page "/"
    Then I should be on "/admin/"
    And I create a new "NDR-disabled" "Admin" user "John" "Doe" with email "behat-admin-user@publicguardian.gov.uk" and postcode "AB12CD"
    Then I should see "behat-admin-user@publicguardian.gov.uk" in the "users" region
    And the response status code should be 200

  Scenario: login and add user (admin)
    When I activate the admin user "behat-admin-user@publicguardian.gov.uk" with password "DigidepsPass1234"
    Then I should not see an "#error-summary" element
    And I should be on "/login"
    And I should see "Sign in to your new account"

  Scenario: Admins cannot add super admins
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I follow "Add new user"
    And I fill in "admin_roleType_1" with "staff"
    Then I should see "Admin"
    And I should not see "Super admin"

  Scenario: Super admins can add super admins
    Given I am logged in to admin as "super-admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I follow "Add new user"
    And I fill in "admin_roleType_1" with "staff"
    Then I should see "Admin"
    And I should see "Super admin"

  Scenario: login and add NDR enabled lay user with co-deputy
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I create a new "NDR-enabled" "Lay Deputy" user "Joe" "Bloggs" with email "joe.bloggs@publicguardian.gov.uk" and postcode "SW1"
    Then I should see "joe.bloggs@publicguardian.gov.uk" in the "users" region
    And the response status code should be 200
    Then I add the following users to CASREC:
      | Case     | Surname | Deputy No | Dep Surname | Dep Postcode | Typeofrep |
      | 12345XYZ | Smith   | D003      | Bloggs      | SW1          | OPG102    |
      | 12345XYZ | Smith   | D004      | Doe         | SW1          | OPG102    |
    When I activate the user "joe.bloggs@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I go to "logout"
    Given I am logged in as "joe.bloggs@publicguardian.gov.uk" with password "DigidepsPass1234"
    Then the url should match "/user/details"
    When I fill in the following:
      | user_details_firstname       | Joe                              |
      | user_details_lastname        | Bloggs                           |
      | user_details_address1        | address1                         |
      | user_details_addressPostcode | sw1                              |
      | user_details_addressCountry  | GB                               |
      | user_details_phoneMain       | 0000000000                       |
      | user_details_email           | joe.bloggs@publicguardian.gov.uk |
    And I press "user_details_save"
    Then the url should match "/client/add"
    Then I fill in the following:
      | client_firstname       | Fred     |
      | client_lastname        | Smith    |
      | client_courtDate_day   | 30       |
      | client_courtDate_month | 12       |
      | client_courtDate_year  | 2016     |
      | client_address         | address1 |
      | client_country         | GB       |
      | client_postcode        | SW1      |
      | client_caseNumber      | 12345XYZ |
    And I press "client_save"
    Then the url should match "/ndr"
    And I should see the "invite-codeputy-button" link

  Scenario: Can follow links to lay upload page
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I am on admin page "/admin"
    And I follow "Upload users"
    And I fill in "form_type_0" with "lay"
    And I press "Continue"
    Then I should be on "/admin/casrec-upload"

  Scenario: Can follow links to org upload page
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I am on admin page "/admin"
    And I follow "Upload users"
    And I fill in "form_type_1" with "org"
    And I press "Continue"
    Then I should be on "/admin/org-csv-upload"

  Scenario: Report submissions CSV download No dates
    Given I am logged in to admin as "behat-admin-user@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I go to admin page "/admin/stats"
    And I click on "submit-and-download"
    And the response status code should be 200
    And the response should have the "Content-Type" header containing "application/octet-stream"
    And the response should have the "Content-Disposition" header containing "cwsdigidepsopg00001"
    And the response should have the "Content-Disposition" header containing ".dat"

  Scenario Outline: Downloading Report Submissions with start and end dates
    Given I am logged in to admin as "behat-admin-user@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I go to admin page "/admin/stats"
    And I fill in "admin_startDate_day" with "<from_day>"
    And I fill in "admin_startDate_month" with "<from_month>"
    And I fill in "admin_startDate_year" with "<from_year>"
    And I fill in "admin_endDate_day" with "<to_day>"
    And I fill in "admin_endDate_month" with "<to_month>"
    And I fill in "admin_endDate_year" with "<to_year>"
    And I click on "submit-and-download"
    And the response status code should be 200
    And the response should have the "Content-Type" header containing "application/octet-stream"
    And the response should have the "Content-Disposition" header containing ".dat"
    Examples:
      | from_day | from_month | from_year | to_day | to_month | to_year |
      | 12       | 12         | 2018      | 12     | 12       | 2018    |
      | 12       | 12         | 2018      | 13     | 12       | 2018    |

  Scenario: Attempting to download Report Submissions with an end date earlier than the start date
    Given I am logged in to admin as "behat-admin-user@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I go to admin page "/admin/stats"
    And I fill in "admin_startDate_day" with "12"
    And I fill in "admin_startDate_month" with "12"
    And I fill in "admin_startDate_year" with "2018"
    And I fill in "admin_endDate_day" with "11"
    And I fill in "admin_endDate_month" with "12"
    And I fill in "admin_endDate_year" with "2018"
    And I click on "submit-and-download"
    Then I should see "Check the end date: it cannot be before the start date"

  Scenario: Can access metrics and set a period
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I go to admin page "/admin/stats/metrics"
    Then the response status code should be 200
    When I fill in "admin_period_1" with "this-year"
    And I press "Update date range"
    Then the response status code should be 200

  Scenario: change user password on admin area
    Given I am logged in to admin as "behat-admin-user@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "user-account"
    Then the response status code should be 200
    And I click on "password-edit"
    Then the response status code should be 200
    # wrong old password
    When I fill in "change_password_current_password" with "this.is.the.wrong.password"
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_current_password     |
      | change_password_password_first |
    # invalid new password
    When I fill in the following:
      | change_password_current_password      | DigidepsPass1234 |
      | change_password_password_first  | 1        |
      | change_password_password_second | 2        |
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first |
    # unmatching new passwords
    When I fill in the following:
      | change_password_current_password      | DigidepsPass1234  |
      | change_password_password_first  | DigidepsPass1234  |
      | change_password_password_second | DigidepsPass12345 |
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first |
    #empty password
    When I fill in the following:
      | change_password_current_password      | DigidepsPass1234 |
      | change_password_password_first  |          |
      | change_password_password_second |          |
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first |
    # too common password
    When I fill in the following:
      | change_password_current_password      | DigidepsPass1234 |
      | change_password_password_first  | Password123 |
      | change_password_password_second | Password123 |
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first |
    # valid new password
    When I fill in the following:
      | change_password_current_password      | DigidepsPass1234  |
      | change_password_password_first  | DigidepsPass12345 |
      | change_password_password_second | DigidepsPass12345 |
    And I press "change_password_save"
    Then the form should be valid
    And I should be on "/login"
    And I should see "Sign in with your new password"
      # restore old password (and assert the current password can be used as old password)
    When I am logged in to admin as "behat-admin-user@publicguardian.gov.uk" with password "DigidepsPass12345"
    And I click on "user-account, password-edit"
    And I fill in the following:
      | change_password_current_password      | DigidepsPass12345 |
      | change_password_password_first  | DigidepsPass1234! |
      | change_password_password_second | DigidepsPass1234! |
    And I press "change_password_save"
    Then the form should be valid

  Scenario: service notification
    # test the notification doesn't not appear if not set at all
    Given I delete the "service-notification" app setting
    And I go to "/login"
    Then I should not see the "service-notification-behat" region
    # go to admin page
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I am on admin page "/admin/settings/service-notification"
    Then the following fields should have the corresponding values:
      | setting_content   |  |
      | setting_enabled_0 |  |
      | setting_enabled_1 |  |
    # check validation
    Given I press "setting_save"
    Then the following fields should have an error:
      | setting_content   |
      | setting_enabled_0 |
      | setting_enabled_1 |
    # enable setting
    Given I fill in the following:
      | setting_content   | service-notification-behat |
      | setting_enabled_0 | 1 |
    And I press "setting_save"
    And the form should be valid
    # check deputy lay homepage
    And I go to "/login"
    And I should see "service-notification-behat" in the "service-notification" region
    # disable the notification and check it doesn't display anymore
    Given I am on admin page "/admin/settings/service-notification"
    Given I fill in the following:
      | setting_content   | service-notification-behat |
      | setting_enabled_1 | 0 |
    And I press "setting_save"
    And the form should be valid
    And I go to "/login"
    Then I should not see the "service-notification-behat" region
