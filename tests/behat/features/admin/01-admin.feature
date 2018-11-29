Feature: admin / admin

  Scenario: login and add admin user
    Given emails are sent from "admin" area
    And I reset the email log
    And I am on admin page "/"
    Then I should be on "/login"
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    Given I am on admin page "/"
    Then I should be on "/admin/"
    And I create a new "NDR-disabled" "Admin" user "John" "Doe" with email "behat-admin-user@publicguardian.gov.uk" and postcode "AB12CD"
    Then I should see "behat-admin-user@publicguardian.gov.uk" in the "users" region
    Then the response status code should be 200
    And I should see "OPG Admin" in the "users" region
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-admin-user@publicguardian.gov.uk"
      #When I go to "/logout"
    Given I am on admin page "/logout"


  Scenario: login and add user (admin)
    Given emails are sent from "admin" area
    And I go to "/logout"
      # assert email link doesn't work on admin area
    When I open the "/user/activate/" link from the email on the "deputy" area
    Then the response status code should be 500
      # follow link as it is
    When I open the "/user/activate/" link from the email
    Then the response status code should be 200
      # only testing the correct case, as the form is the same for deputy
      # note: no TC box here
    When I fill in the password fields with "Abcd1234"
    And I press "set_password_save"
    Then I should not see an "#error-summary" element
    And I should be on "/user/details"

  Scenario: check pages
    Given I am logged in to admin as "behat-admin-user@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "csv-upload" in the "navbar" region
    Then the response status code should be 200
    When I go to admin page "/admin/stats"
    Then the response status code should be 200
      # /user no longer exists, changed to main home screen (user page)
    When I am on admin page "/"
    Then the response status code should be 200

  Scenario: Report submissions CSV download No dates
    Given I am logged in to admin as "behat-admin-user@publicguardian.gov.uk" with password "Abcd1234"
    When I go to admin page "/admin/stats"
    And I click on "submit-and-download"
    And the response status code should be 200
    And the response should have the "Content-Type" header containing "text/csv"
    And the response should have the "Content-Disposition" header containing ".csv"
    And the response should contain "id,report_type,deputy_no,email,name,lastname,registration_date,report_due_date,report_date_submitted"

  Scenario: change user password on admin area
    Given I am logged in to admin as "behat-admin-user@publicguardian.gov.uk" with password "Abcd1234"
    And I save the application status into "admin-pasword-change-init"
    And I click on "user-account"
    Then the response status code should be 200
    And I click on "password-edit"
    Then the response status code should be 200
      # wrong old password
    When I fill in "change_password_current_password" with "this.is.the.wrong.password"
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_current_password     |
      | change_password_plain_password_first |
      # invalid new password
    When I fill in the following:
      | change_password_current_password      | Abcd1234 |
      | change_password_plain_password_first  | 1        |
      | change_password_plain_password_second | 2        |
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_plain_password_first |
      # unmatching new passwords
    When I fill in the following:
      | change_password_current_password      | Abcd1234  |
      | change_password_plain_password_first  | Abcd1234  |
      | change_password_plain_password_second | Abcd12345 |
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_plain_password_first |
      #empty password
    When I fill in the following:
      | change_password_current_password      | Abcd1234 |
      | change_password_plain_password_first  |          |
      | change_password_plain_password_second |          |
    And I press "change_password_save"
    Then the following fields should have an error:
      | change_password_plain_password_first |
      # valid new password
    When I fill in the following:
      | change_password_current_password      | Abcd1234  |
      | change_password_plain_password_first  | Abcd12345 |
      | change_password_plain_password_second | Abcd12345 |
    And I press "change_password_save"
    Then the form should be valid
      # restore old password (and assert the current password can be used as old password)
    When I click on "user-account, password-edit"
    And I fill in the following:
      | change_password_current_password      | Abcd12345 |
      | change_password_plain_password_first  | Abcd1234  |
      | change_password_plain_password_second | Abcd1234  |
    And I press "change_password_save"
    Then the form should be valid
    And I should be on "/deputyship-details/your-details/change-password/done"
    And I load the application status from "admin-pasword-change-init"


  Scenario: service notification
    # test the notification doesn't not appear if not set at all
    Given I delete the "service-notification" app setting
    And I go to "/login"
    Then I should not see the "service-notification-behat" region
    # go to admin page
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
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

